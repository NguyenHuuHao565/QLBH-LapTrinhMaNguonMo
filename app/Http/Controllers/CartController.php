<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Models\Product;
use App\Services\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private Cart $cart)
    {
    }

    public function index(): View
    {
        $lines = $this->cart->lines();

        return view('cart.index', [
            'lines' => $lines,
            'total' => $this->cart->total($lines),
            'hasProblem' => $lines->contains(fn ($line) => $line->problem !== null),
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        // Chỉ nhận số lượng; giá luôn lấy từ database
        $quantity = (int) $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ])['quantity'];

        try {
            $this->cart->add($product, $quantity);
        } catch (CheckoutException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', "Đã thêm {$quantity} × “{$product->name}” vào giỏ hàng.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $quantity = (int) $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ])['quantity'];

        try {
            $this->cart->update($product, $quantity);
        } catch (CheckoutException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        return redirect()->route('cart.index')->with('status', 'Đã cập nhật số lượng.');
    }

    public function remove(Product $product): RedirectResponse
    {
        $this->cart->remove($product);

        return redirect()->route('cart.index')->with('status', "Đã xóa “{$product->name}” khỏi giỏ hàng.");
    }
}
