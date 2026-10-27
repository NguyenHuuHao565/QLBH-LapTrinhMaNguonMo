<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Chỉ chủ đơn hàng hoặc ADMIN được xem chi tiết đơn.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() || $order->user_id === $user->id;
    }

    /**
     * Khách chỉ được tự hủy đơn của chính mình khi đơn còn chờ xác nhận và chưa thanh toán
     * (điều kiện được kiểm tra lại trong transaction khi hủy).
     */
    public function cancel(User $user, Order $order): bool
    {
        return $order->user_id === $user->id && $order->isCancellableByCustomer();
    }
}
