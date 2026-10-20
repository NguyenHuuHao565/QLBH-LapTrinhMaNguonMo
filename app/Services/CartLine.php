<?php

namespace App\Services;

use App\Models\Product;

/**
 * Một dòng trong giỏ hàng. Đơn giá luôn lấy từ Product trong database.
 */
final readonly class CartLine
{
    public int $unitPrice;

    public int $subtotal;

    public ?string $problem;

    public function __construct(public Product $product, public int $quantity)
    {
        $this->unitPrice = $product->finalPrice();
        $this->subtotal = $this->unitPrice * $quantity;
        $this->problem = $product->purchaseProblem($quantity);
    }
}
