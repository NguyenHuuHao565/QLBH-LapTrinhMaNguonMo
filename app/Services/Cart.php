<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Giỏ hàng lưu trong session dưới dạng [product_id => quantity].
 * Session chỉ giữ ID và số lượng; giá, tên, tồn kho luôn đọc lại từ database.
 */
class Cart
{
    private const SESSION_KEY = 'cart';

    /**
     * @return array<int, int>
     */
    public function items(): array
    {
        $items = [];

        foreach ((array) session(self::SESSION_KEY, []) as $productId => $quantity) {
            if ((int) $productId > 0 && (int) $quantity > 0) {
                $items[(int) $productId] = (int) $quantity;
            }
        }

        return $items;
    }

    public function count(): int
    {
        return array_sum($this->items());
    }

    public function isEmpty(): bool
    {
        return $this->items() === [];
    }

    public function quantityOf(Product $product): int
    {
        return $this->items()[$product->id] ?? 0;
    }

    public function add(Product $product, int $quantity): void
    {
        $this->set($product, $this->quantityOf($product) + $quantity);
    }

    public function update(Product $product, int $quantity): void
    {
        $this->set($product, $quantity);
    }

    public function remove(Product $product): void
    {
        session()->forget(self::SESSION_KEY.'.'.$product->id);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Các dòng hàng với giá & tồn kho hiện tại từ database.
     * Sản phẩm đã bị xóa khỏi database được loại khỏi giỏ.
     *
     * @return Collection<int, CartLine>
     */
    public function lines(): Collection
    {
        $items = $this->items();

        $products = Product::with('category')->whereKey(array_keys($items))->get()->keyBy('id');

        foreach (array_diff_key($items, $products->all()) as $missingId => $_) {
            session()->forget(self::SESSION_KEY.'.'.$missingId);
        }

        return $products->map(fn (Product $product) => new CartLine($product, $items[$product->id]))->values();
    }

    /**
     * @param  Collection<int, CartLine>  $lines
     */
    public function total(Collection $lines): int
    {
        return $lines->sum('subtotal');
    }

    private function set(Product $product, int $quantity): void
    {
        $product->loadMissing('category');

        if ($quantity < 1) {
            throw new CheckoutException('Số lượng phải lớn hơn 0.');
        }

        if ($problem = $product->purchaseProblem($quantity)) {
            throw new CheckoutException("{$product->name}: {$problem}");
        }

        session()->put(self::SESSION_KEY.'.'.$product->id, $quantity);
    }
}
