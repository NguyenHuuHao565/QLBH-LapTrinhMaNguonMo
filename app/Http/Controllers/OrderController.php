<?php

namespace App\Http\Controllers;

use App\Exceptions\OrderStatusException;
use App\Models\Order;
use App\Services\OrderStatusUpdater;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    public const CANCEL_REASONS = [
        'Đổi ý, không muốn mua nữa',
        'Muốn thay đổi sản phẩm / số lượng',
        'Đặt nhầm thông tin người nhận',
        'Tìm được nơi khác phù hợp hơn',
    ];

    public function index(Request $request): View
    {
        $orders = $request->user()->orders()
            ->withCount('items')
            ->latest('placed_at')
            ->latest('id')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        return view('orders.show', [
            'order' => $order->load('items.product'),
            'cancelReasons' => self::CANCEL_REASONS,
        ]);
    }

    public function cancel(Request $request, Order $order, OrderStatusUpdater $updater): RedirectResponse
    {
        Gate::authorize('view', $order);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ], [], ['reason' => 'lý do hủy']);

        try {
            $order = $updater->cancelByCustomer($order, $request->user(), $data['reason'] ?? null);
        } catch (OrderStatusException $e) {
            return redirect()->route('orders.show', $order)->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)->with('status', "Đã hủy đơn {$order->code}. Cảm ơn bạn đã báo cho Bloomy!");
    }
}
