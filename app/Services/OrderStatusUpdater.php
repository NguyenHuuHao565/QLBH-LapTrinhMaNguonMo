<?php

namespace App\Services;

use App\Exceptions\OrderStatusException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Chuyển trạng thái đơn hàng theo Order::TRANSITIONS, trong một transaction.
 *
 * - Khóa dòng đơn hàng để các thao tác đồng thời không chồng lên nhau;
 *   $expectedFrom là trạng thái admin đang nhìn thấy, khác với thực tế thì từ chối.
 * - Hủy đơn: hoàn lại tồn kho cho các sản phẩm còn tồn tại.
 * - Hoàn thành: đơn COD đã thu tiền nên chuyển sang 'paid';
 *   đơn chuyển khoản phải được xác nhận đã nhận tiền trước.
 * - Không bao giờ sửa order_items (giá, số lượng lúc mua được giữ nguyên).
 */
class OrderStatusUpdater
{
    public function transition(Order $order, string $to, string $expectedFrom, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $to, $expectedFrom, $reason) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== $expectedFrom) {
                throw new OrderStatusException(
                    "Đơn {$locked->code} vừa được chuyển sang “{$locked->statusLabel()}” bởi người khác. Vui lòng kiểm tra lại."
                );
            }

            if (! $locked->canTransitionTo($to)) {
                $toLabel = Order::STATUSES[$to] ?? $to;

                throw new OrderStatusException("Không thể chuyển đơn từ “{$locked->statusLabel()}” sang “{$toLabel}”.");
            }

            if ($to === 'completed') {
                if ($locked->isBankTransfer() && ! $locked->isPaid()) {
                    throw new OrderStatusException('Đơn chuyển khoản chưa được xác nhận đã nhận tiền, chưa thể hoàn thành.');
                }

                if (! $locked->isPaid()) {
                    $locked->forceFill(['payment_status' => 'paid', 'paid_at' => now()]); // COD: thu tiền khi giao
                }
            }

            if ($to === 'cancelled') {
                $this->cancel($locked, User::ROLE_ADMIN, $reason);
            }

            $locked->status = $to;
            $locked->save();

            return $locked;
        });
    }

    /**
     * Khách tự hủy đơn của mình: chỉ khi đơn còn "Chờ xác nhận" và chưa thanh toán.
     */
    public function cancelByCustomer(Order $order, User $customer, ?string $reason): Order
    {
        return DB::transaction(function () use ($order, $customer, $reason) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->user_id !== $customer->id) {
                throw new OrderStatusException('Bạn không thể hủy đơn hàng này.');
            }

            if (! $locked->isCancellableByCustomer()) {
                throw new OrderStatusException(
                    "Đơn {$locked->code} đang ở trạng thái “{$locked->statusLabel()}”"
                    .($locked->isPaid() ? ' và đã thanh toán' : '')
                    .' nên không thể tự hủy. Vui lòng liên hệ cửa hàng để được hỗ trợ.'
                );
            }

            $this->cancel($locked, User::ROLE_CUSTOMER, $reason);
            $locked->status = 'cancelled';
            $locked->save();

            return $locked;
        });
    }

    /**
     * Admin xác nhận đã nhận tiền chuyển khoản.
     */
    public function markPaid(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isBankTransfer()) {
                throw new OrderStatusException('Chỉ xác nhận thanh toán thủ công cho đơn chuyển khoản. Đơn COD được tính đã thanh toán khi hoàn thành.');
            }

            if ($locked->isPaid()) {
                throw new OrderStatusException("Đơn {$locked->code} đã được xác nhận thanh toán trước đó.");
            }

            if ($locked->status === 'cancelled') {
                throw new OrderStatusException("Đơn {$locked->code} đã bị hủy.");
            }

            $locked->forceFill(['payment_status' => 'paid', 'paid_at' => now()])->save();

            return $locked;
        });
    }

    private function cancel(Order $order, string $by, ?string $reason): void
    {
        $order->forceFill([
            'cancel_reason' => filled($reason) ? $reason : null,
            'cancelled_by' => $by,
            'cancelled_at' => now(),
        ]);

        $this->restock($order);
    }

    private function restock(Order $order): void
    {
        $quantities = $order->items()
            ->whereNotNull('product_id')
            ->get()
            ->groupBy('product_id')
            ->map(fn ($items) => $items->sum('quantity'));

        if ($quantities->isEmpty()) {
            return;
        }

        // Khóa sản phẩm theo thứ tự id (giống lúc đặt hàng) để tránh deadlock
        Product::whereKey($quantities->keys())->orderBy('id')->lockForUpdate()->get()
            ->each(fn (Product $product) => $product->increment('stock', $quantities[$product->id]));
    }
}
