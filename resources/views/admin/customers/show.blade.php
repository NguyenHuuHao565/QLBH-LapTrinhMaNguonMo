@php($money = fn ($value) => number_format($value, 0, ',', '.').'₫')

<x-admin-layout :title="$customer->name">
    <x-slot:actions>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Danh sách khách hàng</a>
    </x-slot:actions>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">Thông tin cơ bản</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5">Email</dt><dd class="col-7 text-break">{{ $customer->email }}</dd>
                        <dt class="col-5">Điện thoại</dt><dd class="col-7">{{ $customer->phone ?: '—' }}</dd>
                        <dt class="col-5">Địa chỉ</dt><dd class="col-7">{{ $customer->address ?: '—' }}</dd>
                        <dt class="col-5">Ngày đăng ký</dt><dd class="col-7">{{ $customer->created_at->format('d/m/Y') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white fw-semibold">Tổng quan mua hàng</div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between"><span>Tổng số đơn</span><strong>{{ $stats['orders'] }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>Đơn hoàn thành</span><strong>{{ $stats['completed'] }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>Đơn đã hủy</span><strong>{{ $stats['cancelled'] }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>Đã mua (đơn hoàn thành)</span><strong>{{ $money($stats['revenue']) }}</strong></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Lịch sử đơn hàng</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Mã đơn</th><th>Ngày đặt</th><th class="text-center">SP</th><th class="text-end">Tổng tiền</th><th>Trạng thái</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold text-decoration-none">{{ $order->code }}</a></td>
                                    <td class="small">{{ $order->placed_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">{{ $order->items_count }}</td>
                                    <td class="text-end text-nowrap">{{ $money($order->total) }}</td>
                                    <td><x-admin-order-status :order="$order" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Khách hàng chưa có đơn hàng.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $orders->links() }}</div>
        </div>
    </div>
</x-admin-layout>
