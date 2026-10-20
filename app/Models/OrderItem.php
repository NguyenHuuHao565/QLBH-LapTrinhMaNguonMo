<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// subtotal không cho gán hàng loạt: luôn được tính lại khi lưu
#[Fillable(['order_id', 'product_id', 'product_name', 'price', 'quantity'])]
class OrderItem extends Model
{
    protected static function booted(): void
    {
        static::saving(function (OrderItem $item) {
            $item->subtotal = $item->price * $item->quantity;
        });
    }

    /**
     * Tạo dòng hàng từ sản phẩm: chụp lại tên và giá tại thời điểm mua,
     * để đơn cũ không đổi khi sản phẩm bị sửa giá hoặc ngừng kinh doanh.
     */
    public static function fromProduct(Product $product, int $quantity): static
    {
        return new static([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->finalPrice(),
            'quantity' => $quantity,
        ]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'quantity' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
