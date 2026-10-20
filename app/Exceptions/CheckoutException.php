<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Lỗi nghiệp vụ khi thêm giỏ hàng / đặt hàng (hết hàng, vượt tồn kho, ngừng bán...).
 * Thông điệp được hiển thị trực tiếp cho khách.
 */
class CheckoutException extends RuntimeException
{
}
