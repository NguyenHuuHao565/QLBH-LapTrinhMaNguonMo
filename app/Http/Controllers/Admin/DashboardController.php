<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Thống kê theo trạng thái: một truy vấn GROUP BY trên dữ liệu thật
        $byStatus = Order::query()
            ->selectRaw('status, COUNT(*) as orders_count, COALESCE(SUM(total), 0) as orders_total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $statusRows = collect(Order::STATUSES)->map(fn ($label, $status) => [
            'status' => $status,
            'label' => $label,
            'count' => (int) ($byStatus[$status]->orders_count ?? 0),
            'total' => (int) ($byStatus[$status]->orders_total ?? 0),
        ]);

        $totalOrders = $statusRows->sum('count');

        return view('admin.dashboard', [
            'stats' => [
                'products' => Product::count(),
                'activeProducts' => Product::where('is_active', true)->count(),
                'customers' => User::where('role', User::ROLE_CUSTOMER)->count(),
                'orders' => $totalOrders,
                // Doanh thu: chỉ đơn 'completed'; đơn hủy không bao giờ được tính
                'revenue' => $statusRows->firstWhere('status', Order::REVENUE_STATUS)['total'],
                // Giá trị đơn đang xử lý (chưa phải doanh thu)
                'pipeline' => $statusRows->whereIn('status', ['pending', 'confirmed', 'shipping'])->sum('total'),
            ],
            'statusRows' => $statusRows,
            'totalOrders' => $totalOrders,
            'latestOrders' => Order::with('user')->withCount('items')->latest('placed_at')->latest('id')->take(8)->get(),
            'outOfStock' => Product::where('stock', 0)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
