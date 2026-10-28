@props(['order'])

@php($bank = config('shop.bank'))

{{-- Hướng dẫn chuyển khoản bằng mã QR tĩnh của cửa hàng: khách tự nhập số tiền và nội dung --}}
<div {{ $attributes->merge(['class' => 'bank-transfer']) }}>
    <div class="bank-qr">
        <a href="{{ asset($bank['qr_image']) }}" target="_blank" rel="noopener" title="Bấm để xem mã QR cỡ lớn">
            <img src="{{ asset($bank['qr_image']) }}" alt="Mã QR nhận tiền của {{ $bank['account_name'] }}" width="300" height="319">
        </a>
        <div class="small text-muted text-center mt-1"><i class="bi bi-zoom-in"></i> Bấm vào mã để phóng to</div>
    </div>
    <div class="bank-info">
        <h3>Quét mã để chuyển khoản</h3>
        <p class="text-muted small mb-3">Mở app MoMo hoặc app ngân hàng bất kỳ → Quét mã QR → nhập <strong>đúng số tiền</strong> và <strong>nội dung</strong> bên dưới.</p>
        <dl>
            <dt>Nhận qua</dt><dd>{{ $bank['name'] }}</dd>
            <dt>Chủ tài khoản</dt><dd class="fw-semibold">{{ $bank['account_name'] }}</dd>
            <dt>Số tài khoản</dt><dd>{{ $bank['account_masked'] }}</dd>
            <dt>Số tiền</dt><dd class="fw-semibold text-bloom fs-5">{{ number_format($order->total, 0, ',', '.') }}₫</dd>
            <dt>Nội dung</dt><dd><code class="fs-5">{{ $order->transferContent() }}</code></dd>
        </dl>
        <p class="small mb-0"><i class="bi bi-info-circle"></i> Ghi đúng mã đơn ở phần nội dung để cửa hàng đối soát. Đơn được xử lý sau khi cửa hàng xác nhận đã nhận tiền.</p>
    </div>
</div>
