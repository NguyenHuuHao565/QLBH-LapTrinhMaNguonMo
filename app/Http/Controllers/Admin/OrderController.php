<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\OrderStatusException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStatusUpdater;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', Rule::in(array_keys(Order::STATUSES))],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ], [
            'to.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
        ], [
            'q' => 'mã đơn', 'status' => 'trạng thái', 'from' => 'từ ngày', 'to' => 'đến ngày',
        ]);

        $search = trim($filters['q'] ?? '');

        $orders = Order::query()
            ->with('user')
            ->withCount('items')
            ->when($search !== '', fn ($q) => $q->where('code', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('placed_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('placed_at', '<=', $to))
            ->latest('placed_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => [...['q' => '', 'status' => null, 'from' => null, 'to' => null], ...$filters],
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items.product', 'user']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order, OrderStatusUpdater $updater): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            // Trạng thái admin đang nhìn thấy, để phát hiện cập nhật chồng chéo
            'from' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'cancel_reason' => ['nullable', 'string', 'max:255'],
        ], [], ['status' => 'trạng thái mới', 'cancel_reason' => 'lý do hủy']);

        try {
            $order = $updater->transition($order, $data['status'], $data['from'], $data['cancel_reason'] ?? null);
        } catch (OrderStatusException $e) {
            return redirect()->route('admin.orders.show', $order)->with('error', $e->getMessage());
        }

        $message = "Đã chuyển đơn {$order->code} sang “{$order->statusLabel()}”.";
        if ($order->status === 'cancelled') {
            $message .= ' Tồn kho đã được hoàn lại.';
            if ($order->isPaid()) {
                $message .= ' Khách đã chuyển khoản — cần hoàn tiền cho khách.';
            }
        }

        return redirect()->route('admin.orders.show', $order)->with('status', $message);
    }

    public function markPaid(Order $order, OrderStatusUpdater $updater): RedirectResponse
    {
        try {
            $order = $updater->markPaid($order);
        } catch (OrderStatusException $e) {
            return redirect()->route('admin.orders.show', $order)->with('error', $e->getMessage());
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('status', "Đã xác nhận nhận tiền chuyển khoản cho đơn {$order->code}.");
    }
}
