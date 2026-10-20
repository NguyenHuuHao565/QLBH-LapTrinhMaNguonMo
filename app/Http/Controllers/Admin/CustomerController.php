<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $this->searchTerm($request);

        $customers = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $q->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('phone', 'like', $like));
            })
            ->withCount('orders')
            ->withSum(['orders as revenue' => fn ($q) => $q->countedInRevenue()], 'total')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'search'));
    }

    public function show(User $customer): View
    {
        // Trang này chỉ dành cho tài khoản khách hàng
        abort_unless($customer->role === User::ROLE_CUSTOMER, 404);

        $orders = $customer->orders()->withCount('items')->latest('placed_at')->latest('id')->paginate(10);

        return view('admin.customers.show', [
            'customer' => $customer,
            'orders' => $orders,
            'stats' => [
                'orders' => $customer->orders()->count(),
                'completed' => $customer->orders()->countedInRevenue()->count(),
                'cancelled' => $customer->orders()->where('status', 'cancelled')->count(),
                'revenue' => (int) $customer->orders()->countedInRevenue()->sum('total'),
            ],
        ]);
    }
}
