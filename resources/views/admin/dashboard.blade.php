@php($money = fn ($value) => number_format($value, 0, ',', '.').'₫')

<x-admin-layout title="Tổng quan">
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-cash-coin stat-icon"></i>
                    <div>
                        <div class="stat-value">{{ $money($stats['revenue']) }}</div>
                        <div class="text-muted small">Doanh thu <span class="d-block">(đơn hoàn thành)</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('admin.orders.index') }}" class="card stat-card h-100 text-reset text-decoration-none">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-receipt stat-icon"></i>
                    <div>
                        <div class="stat-value">{{ number_format($stats['orders'], 0, ',', '.') }}</div>
                        <div class="text-muted small">Đơn hàng <span class="d-block">đang xử lý: {{ $money($stats['pipeline']) }}</span></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('admin.customers.index') }}" class="card stat-card h-100 text-reset text-decoration-none">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-people stat-icon"></i>
                    <div>
                        <div class="stat-value">{{ number_format($stats['customers'], 0, ',', '.') }}</div>
                        <div class="text-muted small">Khách hàng</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('admin.products.index') }}" class="card stat-card h-100 text-reset text-decoration-none">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-flower1 stat-icon"></i>
                    <div>
                        <div class="stat-value">{{ number_format($stats['products'], 0, ',', '.') }}</div>
                        <div class="text-muted small">Sản phẩm <span class="d-block">{{ $stats['activeProducts'] }} đang bán</span></div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Đơn hàng mới nhất</span>
                    <a href="{{ route('admin.orders.index') }}" class="small">Xem tất cả</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Mã đơn</th><th>Khách hàng</th><th>Ngày đặt</th><th class="text-end">Tổng tiền</th><th>Trạng thái</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($latestOrders as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold text-decoration-none">{{ $order->code }}</a></td>
                                    <td>{{ $order->user?->name ?? $order->recipient_name }}</td>
                                    <td class="text-nowrap small">{{ $order->placed_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end text-nowrap">{{ $money($order->total) }}</td>
                                    <td><x-admin-order-status :order="$order" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Chưa có đơn hàng nào.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">Đơn hàng theo trạng thái</div>
                <ul class="list-group list-group-flush">
                    @foreach ($statusRows as $row)
                        @php($percent = $totalOrders ? round($row['count'] * 100 / $totalOrders) : 0)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between small mb-1">
                                <a href="{{ route('admin.orders.index', ['status' => $row['status']]) }}" class="text-decoration-none">{{ $row['label'] }}</a>
                                <span><strong>{{ $row['count'] }}</strong> · {{ $money($row['total']) }}</span>
                            </div>
                            {{-- Thanh tỉ lệ bằng CSS, không cần thư viện biểu đồ --}}
                            <div class="progress" style="height: 6px" role="progressbar" aria-label="{{ $row['label'] }}"
                                 aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar bg-{{ \App\Models\Order::STATUS_COLORS[$row['status']] }}" style="width: {{ $percent }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="card-footer bg-white small text-muted">
                    Doanh thu chỉ tính đơn <strong>Hoàn thành</strong>. Đơn <strong>Đã hủy</strong> không được tính.
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white fw-semibold">Sản phẩm hết hàng</div>
                <ul class="list-group list-group-flush">
                    @forelse ($outOfStock as $product)
                        <li class="list-group-item"><a href="{{ route('admin.products.edit', $product) }}" class="text-decoration-none">{{ $product->name }}</a></li>
                    @empty
                        <li class="list-group-item text-muted">Không có sản phẩm nào hết hàng.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-admin-layout>
