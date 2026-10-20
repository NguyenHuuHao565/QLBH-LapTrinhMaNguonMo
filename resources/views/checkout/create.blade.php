<x-layout title="Thanh toán">
    <section class="page-head page-head-sm">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cart.index') }}">Giỏ hàng</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Thanh toán</li>
                </ol>
            </nav>
            <h1 class="page-title">Thông tin đặt hàng</h1>
        </div>
    </section>

    <section class="section pt-4">
        <div class="container">
            <form action="{{ route('checkout.store') }}" method="post" novalidate data-submit-once>
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', $idempotencyKey) }}">

                @error('idempotency_key')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="info-card">
                            <h2 class="info-card-title">Người nhận</h2>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="recipient_name" class="form-label">Họ tên người nhận <span class="text-danger">*</span></label>
                                    <input type="text" id="recipient_name" name="recipient_name" maxlength="100" required
                                           value="{{ old('recipient_name', $user->name) }}"
                                           class="form-control @error('recipient_name') is-invalid @enderror">
                                    @error('recipient_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="recipient_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="tel" id="recipient_phone" name="recipient_phone" required
                                           value="{{ old('recipient_phone', $user->phone) }}" placeholder="0901234567"
                                           class="form-control @error('recipient_phone') is-invalid @enderror">
                                    @error('recipient_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label for="shipping_address" class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                                    <input type="text" id="shipping_address" name="shipping_address" maxlength="255" required
                                           value="{{ old('shipping_address', $user->address) }}" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"
                                           class="form-control @error('shipping_address') is-invalid @enderror">
                                    @error('shipping_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="delivery_date" class="form-label">Ngày giao mong muốn</label>
                                    <input type="date" id="delivery_date" name="delivery_date" min="{{ today()->toDateString() }}"
                                           max="{{ today()->addDays(60)->toDateString() }}" value="{{ old('delivery_date') }}"
                                           class="form-control @error('delivery_date') is-invalid @enderror">
                                    @error('delivery_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label for="note" class="form-label">Ghi chú / lời nhắn trên thiệp</label>
                                    <textarea id="note" name="note" rows="3" maxlength="500"
                                              class="form-control @error('note') is-invalid @enderror">{{ old('note') }}</textarea>
                                    @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <h2 class="info-card-title mt-4">Thanh toán</h2>
                            <div class="form-check payment-option">
                                <input class="form-check-input" type="radio" name="payment_method" id="pay_cod" value="cod" checked>
                                <label class="form-check-label" for="pay_cod">
                                    <strong>Thanh toán khi nhận hàng (COD)</strong>
                                    <span class="d-block text-muted small">Bạn trả tiền mặt cho nhân viên giao hoa khi nhận hàng.</span>
                                </label>
                            </div>
                            @error('payment_method') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="summary-card">
                            <h2 class="summary-title">Đơn hàng ({{ $lines->sum('quantity') }} sản phẩm)</h2>
                            @foreach ($lines as $line)
                                <div class="summary-item">
                                    <span>{{ $line->product->name }} <span class="text-muted">× {{ $line->quantity }}</span></span>
                                    <span>{{ number_format($line->subtotal, 0, ',', '.') }}₫</span>
                                </div>
                            @endforeach
                            <div class="summary-row"><span>Phí giao hàng</span><span>Miễn phí</span></div>
                            <div class="summary-row summary-total"><span>Tổng thanh toán</span><span>{{ number_format($total, 0, ',', '.') }}₫</span></div>
                            <p class="text-muted small mb-0">Giá và tồn kho được kiểm tra lại khi bạn bấm đặt hàng.</p>

                            <button type="submit" class="btn btn-bloom btn-lg w-100 mt-3" data-loading-text="Đang đặt hàng...">
                                <i class="bi bi-bag-check me-1"></i> Đặt hàng
                            </button>
                            <a href="{{ route('cart.index') }}" class="btn btn-link w-100 mt-1">Quay lại giỏ hàng</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</x-layout>
