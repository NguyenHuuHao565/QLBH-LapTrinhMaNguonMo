<x-layout :title="'Đơn hàng '.$order->code">
    <section class="page-head page-head-sm">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Đơn hàng</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $order->code }}</li>
                </ol>
            </nav>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <h1 class="page-title">Đơn hàng {{ $order->code }}</h1>
                <span class="status-badge status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
            </div>
            <p class="page-lead">Đặt lúc {{ $order->placed_at->format('H:i, d/m/Y') }}</p>
        </div>
    </section>

    <section class="section pt-4">
        <div class="container">
            @if ($order->awaitsBankTransfer())
                <div class="info-card mb-4">
                    <h2 class="info-card-title">Thanh toán chuyển khoản</h2>
                    <x-bank-transfer :order="$order" />
                </div>
            @endif

            @if ($order->status === 'cancelled')
                <div class="alert alert-secondary">
                    <i class="bi bi-x-circle me-1"></i>
                    Đơn đã được hủy {{ $order->cancelled_by === \App\Models\User::ROLE_CUSTOMER ? 'bởi bạn' : 'bởi cửa hàng' }}
                    @if ($order->cancelled_at) lúc {{ $order->cancelled_at->format('H:i, d/m/Y') }}@endif.
                    @if ($order->cancel_reason) Lý do: “{{ $order->cancel_reason }}”.@endif
                    @if ($order->isPaid()) Cửa hàng sẽ liên hệ để hoàn tiền chuyển khoản cho bạn.@endif
                </div>
            @endif

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="info-card">
                        <h2 class="info-card-title">Sản phẩm</h2>
                        @foreach ($order->items as $item)
                            <div class="summary-item">
                                <span>
                                    {{-- Tên & giá lấy từ dữ liệu đã lưu lúc mua, không phụ thuộc sản phẩm hiện tại --}}
                                    @if ($item->product?->is_active)
                                        <a href="{{ route('products.show', $item->product) }}">{{ $item->product_name }}</a>
                                    @else
                                        {{ $item->product_name }}
                                    @endif
                                    <span class="text-muted d-block small">{{ number_format($item->price, 0, ',', '.') }}₫ × {{ $item->quantity }}</span>
                                </span>
                                <span class="fw-semibold">{{ number_format($item->subtotal, 0, ',', '.') }}₫</span>
                            </div>
                        @endforeach
                        <div class="summary-row"><span>Phí giao hàng</span><span>Miễn phí</span></div>
                        <div class="summary-row summary-total"><span>Tổng tiền</span><span>{{ number_format($order->total, 0, ',', '.') }}₫</span></div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="info-card">
                        <h2 class="info-card-title">Giao hàng & thanh toán</h2>
                        <dl class="info-list">
                            <dt>Người nhận</dt>
                            <dd>{{ $order->recipient_name }}</dd>
                            <dt>Điện thoại</dt>
                            <dd>{{ $order->recipient_phone }}</dd>
                            <dt>Địa chỉ</dt>
                            <dd>{{ $order->shipping_address }}</dd>
                            <dt>Ngày giao</dt>
                            <dd>{{ $order->delivery_date?->format('d/m/Y') ?? 'Sớm nhất có thể' }}</dd>
                            <dt>Ghi chú</dt>
                            <dd>{!! $order->note ? nl2br(e($order->note)) : '—' !!}</dd>
                            <dt>Thanh toán</dt>
                            <dd>{{ $order->paymentMethodLabel() }}</dd>
                            <dt>Tình trạng</dt>
                            <dd><span class="status-badge payment-{{ $order->payment_status }}">{{ $order->paymentStatusLabel() }}</span></dd>
                        </dl>
                    </div>

                    @can('cancel', $order)
                        <div class="info-card mt-4">
                            <h2 class="info-card-title">Hủy đơn hàng</h2>
                            <p class="text-muted small">Bạn có thể tự hủy khi đơn còn <strong>Chờ xác nhận</strong>. Sau khi cửa hàng xác nhận, vui lòng gọi hotline để được hỗ trợ.</p>
                            <form action="{{ route('orders.cancel', $order) }}" method="post" data-confirm="Bạn chắc chắn muốn hủy đơn {{ $order->code }}?">
                                @csrf
                                @method('PATCH')
                                <label for="reason" class="form-label">Lý do hủy <span class="text-muted">(không bắt buộc)</span></label>
                                <select id="reason" name="reason" class="form-select mb-3 @error('reason') is-invalid @enderror">
                                    <option value="">— Chọn lý do —</option>
                                    @foreach ($cancelReasons as $reason)
                                        <option value="{{ $reason }}">{{ $reason }}</option>
                                    @endforeach
                                </select>
                                @error('reason') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror
                                <button type="submit" class="btn btn-outline-danger w-100"><i class="bi bi-x-circle me-1"></i> Hủy đơn hàng</button>
                            </form>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </section>
</x-layout>
