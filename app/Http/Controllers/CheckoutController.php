<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\Cart;
use App\Services\OrderPlacer;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(private Cart $cart)
    {
    }

    public function create(Request $request): View|RedirectResponse
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        if ($lines->contains(fn ($line) => $line->problem !== null)) {
            return redirect()->route('cart.index')->with('error', 'Vui lòng điều chỉnh các sản phẩm không đủ hàng trước khi đặt hàng.');
        }

        return view('checkout.create', [
            'lines' => $lines,
            'total' => $this->cart->total($lines),
            'user' => $request->user(),
            // Mỗi lần mở trang thanh toán cấp một khóa mới; gửi lại cùng khóa sẽ không tạo đơn thứ hai
            'idempotencyKey' => (string) Str::uuid(),
        ]);
    }

    public function store(CheckoutRequest $request, OrderPlacer $placer): RedirectResponse
    {
        $user = $request->user();
        $key = $request->validated('idempotency_key');

        // Gửi lặp (double-click, bấm lại sau khi Back): trả về đơn đã tạo trước đó
        if ($existing = $this->findByKey($user->id, $key)) {
            return redirect()->route('checkout.success', $existing);
        }

        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $shipping = $request->safe()->only(['recipient_name', 'recipient_phone', 'shipping_address', 'note', 'delivery_date']);

        try {
            $order = $placer->place($user, $this->cart->items(), $shipping, $key);
        } catch (CheckoutException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        } catch (UniqueConstraintViolationException $e) {
            // Hai request cùng khóa chạy song song: request đến sau bị chặn bởi UNIQUE
            if ($existing = $this->findByKey($user->id, $key)) {
                return redirect()->route('checkout.success', $existing);
            }

            report($e);

            return back()->withInput()->with('error', 'Không thể tạo đơn hàng. Vui lòng thử lại.');
        } catch (Throwable $e) {
            // Transaction đã rollback: không có đơn nào được tạo, tồn kho không bị trừ
            report($e);

            return back()->withInput()->with('error', 'Đặt hàng thất bại do lỗi hệ thống. Đơn hàng chưa được tạo và giỏ hàng vẫn được giữ nguyên, vui lòng thử lại.');
        }

        $this->cart->clear();

        return redirect()->route('checkout.success', $order);
    }

    public function success(Order $order): View
    {
        Gate::authorize('view', $order);

        return view('checkout.success', ['order' => $order->load('items')]);
    }

    private function findByKey(int $userId, string $key): ?Order
    {
        return Order::where('user_id', $userId)->where('idempotency_key', $key)->first();
    }
}
