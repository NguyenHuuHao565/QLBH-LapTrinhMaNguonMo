<?php

namespace App\Services;

use App\Exceptions\OrderStatusException;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Chuyển trạng thái đơn hàng theo Order::TRANSITIONS, trong một transaction.
 *
 * - Khóa dòng đơn hàng để hai quản trị viên không cập nhật chồng lên nhau;
 *   $expectedFrom là trạng thái admin đang nhìn thấy, khác với thực tế thì từ chối.
 * - Hủy đơn: hoàn lại tồn kho cho các sản phẩm còn tồn tại.
 * - Hoàn thành: đơn COD đã thu tiền nên chuyển payment_status sang 'paid'.
 * - Không bao giờ sửa order_items (giá, số lượng lúc mua được giữ nguyên).
 */
class OrderStatusUpdater
{
    public function transition(Order $order, string $to, string $expectedFrom): Order
    {
        return DB::transaction(function () use ($order, $to, $expectedFrom) {
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

            if ($to === 'cancelled') {
                $this->restock($locked);
            }

            if ($to === 'completed') {
                $locked->payment_status = 'paid';
            }

            $locked->status = $to;
            $locked->save();

            return $locked;
        });
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
