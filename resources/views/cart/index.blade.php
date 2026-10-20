<x-layout title="Giỏ hàng">
    <section class="page-head page-head-sm">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Giỏ hàng</li>
                </ol>
            </nav>
            <h1 class="page-title">Giỏ hàng</h1>
        </div>
    </section>

    <section class="section pt-4">
        <div class="container">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i> {{ $errors->first() }}
                </div>
            @endif

            @if ($lines->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-bag"></i>
                    <h2>Giỏ hàng đang trống</h2>
                    <p>Hãy chọn vài bó hoa thật xinh nhé.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-bloom">Xem sản phẩm</a>
                </div>
            @else
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="cart-list">
                            @foreach ($lines as $line)
                                <div @class(['cart-line', 'has-problem' => $line->problem])>
                                    <a href="{{ route('products.show', $line->product) }}" class="cart-thumb">
                                        <x-product-image :product="$line->product" />
                                    </a>

                                    <div class="cart-info">
                                        <a href="{{ route('products.show', $line->product) }}" class="cart-name">{{ $line->product->name }}</a>
                                        <div class="text-muted small">Đơn giá: {{ number_format($line->unitPrice, 0, ',', '.') }}₫</div>
                                        @if ($line->problem)
                                            <div class="cart-problem"><i class="bi bi-exclamation-circle"></i> {{ $line->problem }}</div>
                                        @endif
                                    </div>

                                    <form class="cart-qty" action="{{ route('cart.update', $line->product) }}" method="post">
                                        @csrf
                                        @method('PATCH')
                                        <div class="qty-stepper qty-sm" data-qty>
                                            <button type="button" data-qty-step="-1" aria-label="Giảm"><i class="bi bi-dash"></i></button>
                                            <input type="number" name="quantity" value="{{ $line->quantity }}" min="1"
                                                   max="{{ max($line->product->stock, 1) }}" inputmode="numeric" required
                                                   aria-label="Số lượng {{ $line->product->name }}">
                                            <button type="button" data-qty-step="1" aria-label="Tăng"><i class="bi bi-plus"></i></button>
                                        </div>
                                        <button type="submit" class="btn btn-link btn-sm p-0">Cập nhật</button>
                                    </form>

                                    <div class="cart-subtotal">{{ number_format($line->subtotal, 0, ',', '.') }}₫</div>

                                    <form action="{{ route('cart.remove', $line->product) }}" method="post" class="cart-remove">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light" aria-label="Xóa {{ $line->product->name }}" title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="summary-card">
                            <h2 class="summary-title">Tóm tắt</h2>
                            <div class="summary-row"><span>Số lượng</span><span>{{ $lines->sum('quantity') }} sản phẩm</span></div>
                            <div class="summary-row"><span>Phí giao hàng</span><span>Miễn phí</span></div>
                            <div class="summary-row summary-total"><span>Tổng tiền</span><span>{{ number_format($total, 0, ',', '.') }}₫</span></div>

                            @if ($hasProblem)
                                <p class="cart-problem mt-3 mb-0">Vui lòng điều chỉnh các sản phẩm được đánh dấu trước khi đặt hàng.</p>
                                <button type="button" class="btn btn-bloom btn-lg w-100 mt-3" disabled>Tiến hành đặt hàng</button>
                            @else
                                <a href="{{ route('checkout.create') }}" class="btn btn-bloom btn-lg w-100 mt-3">Tiến hành đặt hàng <i class="bi bi-arrow-right"></i></a>
                                @guest
                                    <p class="text-muted small text-center mt-2 mb-0">Bạn sẽ được yêu cầu đăng nhập trước khi đặt hàng.</p>
                                @endguest
                            @endif
                            <a href="{{ route('products.index') }}" class="btn btn-link w-100 mt-1">Tiếp tục mua hoa</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-layout>
