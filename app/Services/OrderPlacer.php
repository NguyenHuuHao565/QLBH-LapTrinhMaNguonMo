<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Tạo đơn hàng từ giỏ hàng trong một transaction.
 *
 * - Khóa các dòng sản phẩm (SELECT ... FOR UPDATE) theo thứ tự id để tránh deadlock;
 *   request đặt hàng song song phải chờ, rồi đọc lại tồn kho mới nhất.
 * - Giá lấy từ database tại thời điểm khóa, không dùng dữ liệu từ trình duyệt.
 * - Bất kỳ lỗi nào trong closure đều làm rollback toàn bộ (orders, order_items, tồn kho).
 */
class OrderPlacer
{
    /**
     * @param  array<int, int>  $items  [product_id => quantity]
     * @param  array<string, mixed>  $shipping  thông tin người nhận đã validate
     */
    public function place(User $user, array $items, array $shipping, string $idempotencyKey): Order
    {
        if ($items === []) {
            throw new CheckoutException('Giỏ hàng của bạn đang trống.');
        }

        ksort($items);

        return DB::transaction(function () use ($user, $items, $shipping, $idempotencyKey) {
            $products = Product::query()
                ->whereKey(array_keys($items))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $products->load('category');

            foreach ($items as $productId => $quantity) {
                $product = $products->get($productId)
                    ?? throw new CheckoutException('Một sản phẩm trong giỏ không còn tồn tại. Vui lòng kiểm tra lại giỏ hàng.');

                if ($quantity < 1) {
                    throw new CheckoutException("{$product->name}: Số lượng không hợp lệ.");
                }

                if ($problem = $product->purchaseProblem($quantity)) {
                    throw new CheckoutException("{$product->name}: {$problem}");
                }
            }

            $order = new Order([...$shipping, 'user_id' => $user->id, 'payment_method' => $shipping['payment_method'] ?? 'cod']);
            $order->idempotency_key = $idempotencyKey;
            $order->save();

            foreach ($items as $productId => $quantity) {
                $product = $products->get($productId);

                $order->items()->save(OrderItem::fromProduct($product, $quantity));

                // Trừ kho có điều kiện: lớp bảo vệ thứ hai, không bao giờ để tồn kho âm
                $affected = Product::whereKey($productId)
                    ->where('stock', '>=', $quantity)
                    ->decrement('stock', $quantity);

                if ($affected !== 1) {
                    throw new CheckoutException("{$product->name}: Không đủ hàng trong kho.");
                }
            }

            $order->recalculateTotal();

            return $order;
        }, attempts: 3);
    }
}
