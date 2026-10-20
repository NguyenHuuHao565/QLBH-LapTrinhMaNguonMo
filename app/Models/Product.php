<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['category_id', 'name', 'slug', 'description', 'price', 'sale_price', 'stock', 'image', 'is_active'])]
class Product extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'sale_price' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Sản phẩm khách được thấy: đang kinh doanh và thuộc danh mục đang hoạt động.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->active()->whereHas('category', fn (Builder $q) => $q->active());
    }

    /**
     * Giá bán thực tế: ưu tiên giá khuyến mãi nếu có.
     */
    public function finalPrice(): int
    {
        return $this->sale_price ?: $this->price;
    }

    public function isOnSale(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Lý do không thể mua số lượng $quantity, hoặc null nếu mua được.
     * Dùng chung cho giỏ hàng và lúc tạo đơn (sau khi đã khóa dòng).
     */
    public function purchaseProblem(int $quantity): ?string
    {
        if (! $this->is_active || ! $this->category?->is_active) {
            return 'Sản phẩm đã ngừng kinh doanh.';
        }

        if ($this->stock <= 0) {
            return 'Sản phẩm đã hết hàng.';
        }

        if ($quantity > $this->stock) {
            return "Chỉ còn {$this->stock} sản phẩm trong kho.";
        }

        return null;
    }

    /**
     * URL ảnh sản phẩm, hoặc null nếu chưa có ảnh / file không tồn tại
     * (khi đó view hiển thị minh họa SVG thay thế, tránh ảnh hỏng).
     */
    public function imageUrl(): ?string
    {
        if (blank($this->image)) {
            return null;
        }

        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        // asset() dựng URL theo host đang truy cập (quanlybanhoa.test, localhost...)
        // thay vì phụ thuộc APP_URL
        return Storage::disk('public')->exists($this->image)
            ? asset('storage/'.$this->image)
            : null;
    }
}
