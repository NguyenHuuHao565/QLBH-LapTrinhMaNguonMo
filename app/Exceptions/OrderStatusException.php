<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Lỗi khi chuyển trạng thái đơn hàng không hợp lệ (sai quy tắc, dữ liệu đã cũ...).
 * Thông điệp được hiển thị cho quản trị viên.
 */
class OrderStatusException extends RuntimeException
{
}
