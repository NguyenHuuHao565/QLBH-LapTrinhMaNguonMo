<x-layout title="Đặt hàng thành công">
    <section class="section auth-section">
        <div class="container">
            <div class="success-card">
                <i class="bi bi-check-circle-fill success-icon"></i>
                <h1 class="auth-title">Đặt hàng thành công!</h1>
                <p class="text-muted">Cảm ơn bạn đã tin chọn Bloomy. Chúng tôi sẽ gọi xác nhận đơn trong thời gian sớm nhất.</p>

                <div class="success-code">Mã đơn hàng: <strong>{{ $order->code }}</strong></div>

                <dl class="info-list text-start mt-4">
                    <dt>Người nhận</dt>
                    <dd>{{ $order->recipient_name }} · {{ $order->recipient_phone }}</dd>
                    <dt>Giao đến</dt>
                    <dd>{{ $order->shipping_address }}</dd>
                    <dt>Số sản phẩm</dt>
                    <dd>{{ $order->items->sum('quantity') }}</dd>
                    <dt>Tổng tiền</dt>
                    <dd class="fw-semibold">{{ number_format($order->total, 0, ',', '.') }}₫</dd>
                    <dt>Thanh toán</dt>
                    <dd>{{ $order->paymentMethodLabel() }} — {{ $order->paymentStatusLabel() }}</dd>
                </dl>

                <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
                    <a href="{{ route('orders.show', $order) }}" class="btn btn-bloom">Xem chi tiết đơn hàng</a>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-bloom">Tiếp tục mua hoa</a>
                </div>
            </div>
        </div>
    </section>
</x-layout>
