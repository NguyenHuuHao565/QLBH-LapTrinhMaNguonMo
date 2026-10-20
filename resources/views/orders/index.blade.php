<x-layout title="Đơn hàng của tôi">
    <section class="page-head page-head-sm">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('account.show') }}">Tài khoản</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Đơn hàng</li>
                </ol>
            </nav>
            <h1 class="page-title">Đơn hàng của tôi</h1>
        </div>
    </section>

    <section class="section pt-4">
        <div class="container">
            @if ($orders->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-receipt"></i>
                    <h2>Bạn chưa có đơn hàng nào</h2>
                    <p>Đơn hàng sau khi đặt sẽ hiển thị tại đây.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-bloom">Mua hoa ngay</a>
                </div>
            @else
                <div class="info-card p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 order-table">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Ngày đặt</th>
                                    <th class="text-center">Sản phẩm</th>
                                    <th class="text-end">Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td class="fw-semibold">{{ $order->code }}</td>
                                        <td>{{ $order->placed_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">{{ $order->items_count }}</td>
                                        <td class="text-end text-nowrap">{{ number_format($order->total, 0, ',', '.') }}₫</td>
                                        <td><span class="status-badge status-{{ $order->status }}">{{ $order->statusLabel() }}</span></td>
                                        <td class="text-end"><a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-bloom">Chi tiết</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-4">{{ $orders->links() }}</div>
            @endif
        </div>
    </section>
</x-layout>
