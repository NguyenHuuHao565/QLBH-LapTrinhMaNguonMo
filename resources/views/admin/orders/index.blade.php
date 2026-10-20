<x-admin-layout title="Đơn hàng">
    <form class="row g-2 mb-3 align-items-end" action="{{ route('admin.orders.index') }}" method="get" role="search" novalidate>
        <div class="col-md-3">
            <label for="q" class="form-label small text-muted mb-1">Mã đơn</label>
            <input type="search" id="q" name="q" value="{{ $filters['q'] }}" maxlength="30" placeholder="VD: BL2610..."
                   class="form-control @error('q') is-invalid @enderror">
        </div>
        <div class="col-md-3">
            <label for="status" class="form-label small text-muted mb-1">Trạng thái</label>
            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                <option value="">Tất cả trạng thái</option>
                @foreach (\App\Models\Order::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label for="from" class="form-label small text-muted mb-1">Từ ngày</label>
            <input type="date" id="from" name="from" value="{{ $filters['from'] }}" class="form-control @error('from') is-invalid @enderror">
        </div>
        <div class="col-6 col-md-2">
            <label for="to" class="form-label small text-muted mb-1">Đến ngày</label>
            <input type="date" id="to" name="to" value="{{ $filters['to'] }}" class="form-control @error('to') is-invalid @enderror">
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-outline-secondary flex-grow-1"><i class="bi bi-funnel"></i> Lọc</button>
            @if (array_filter($filters))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-link" title="Xóa lọc"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>

    @if ($errors->any())
        <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
    @endif

    <p class="text-muted small">{{ $orders->total() }} đơn hàng</p>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Người nhận</th>
                        <th>Ngày đặt</th>
                        <th class="text-center">SP</th>
                        <th class="text-end">Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thanh toán</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold text-decoration-none">{{ $order->code }}</a></td>
                            <td>
                                @if ($order->user)
                                    <a href="{{ route('admin.customers.show', $order->user) }}" class="text-decoration-none">{{ $order->user->name }}</a>
                                @else
                                    <span class="text-muted">Tài khoản đã xóa</span>
                                @endif
                            </td>
                            <td>{{ $order->recipient_name }}<div class="small text-muted">{{ $order->recipient_phone }}</div></td>
                            <td class="text-nowrap small">{{ $order->placed_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">{{ $order->items_count }}</td>
                            <td class="text-end text-nowrap">{{ number_format($order->total, 0, ',', '.') }}₫</td>
                            <td><x-admin-order-status :order="$order" /></td>
                            <td class="small">{{ $order->paymentStatusLabel() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Không có đơn hàng phù hợp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $orders->links() }}</div>
</x-admin-layout>
