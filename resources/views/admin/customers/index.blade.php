<x-admin-layout title="Khách hàng">
    <form class="row g-2 mb-3" action="{{ route('admin.customers.index') }}" method="get" role="search">
        <div class="col-sm-8 col-md-6">
            <input type="search" name="q" value="{{ $search }}" class="form-control" maxlength="100"
                   placeholder="Tìm theo tên, email hoặc số điện thoại..." aria-label="Tìm khách hàng">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i> Tìm</button>
            @if ($search !== '')
                <a href="{{ route('admin.customers.index') }}" class="btn btn-link">Xóa lọc</a>
            @endif
        </div>
    </form>

    <p class="text-muted small">{{ $customers->total() }} khách hàng</p>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Khách hàng</th>
                        <th>Điện thoại</th>
                        <th>Ngày đăng ký</th>
                        <th class="text-center">Số đơn</th>
                        <th class="text-end">Đã mua (hoàn thành)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>
                                <a href="{{ route('admin.customers.show', $customer) }}" class="fw-semibold text-decoration-none">{{ $customer->name }}</a>
                                <div class="small text-muted">{{ $customer->email }}</div>
                            </td>
                            <td>{{ $customer->phone ?: '—' }}</td>
                            <td class="small">{{ $customer->created_at->format('d/m/Y') }}</td>
                            <td class="text-center">{{ $customer->orders_count }}</td>
                            <td class="text-end text-nowrap">{{ number_format((int) $customer->revenue, 0, ',', '.') }}₫</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Không có khách hàng phù hợp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $customers->links() }}</div>
</x-admin-layout>
