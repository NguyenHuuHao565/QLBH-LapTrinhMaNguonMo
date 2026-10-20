<footer class="site-footer" id="lien-he">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <a class="brand fs-3" href="{{ route('home') }}"><i class="bi bi-flower1"></i> Bloomy</a>
                <p class="mt-2 mb-0">{{ config('shop.tagline') }}. Mỗi bó hoa được cắm thủ công từ hoa tươi tuyển chọn trong ngày.</p>
            </div>

            <div class="col-6 col-lg-2">
                <h6>Danh mục</h6>
                <ul class="list-unstyled">
                    @foreach ($navCategories as $navCategory)
                        <li><a href="{{ route('products.index', ['category' => $navCategory->slug]) }}">{{ $navCategory->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h6>Cửa hàng</h6>
                <ul class="list-unstyled">
                    <li><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li><a href="{{ route('products.index') }}">Tất cả sản phẩm</a></li>
                    <li><a href="{{ route('products.index', ['sort' => 'price_asc']) }}">Hoa giá tốt</a></li>
                </ul>
            </div>

            <div class="col-lg-4">
                <h6>Liên hệ</h6>
                <ul class="list-unstyled contact-list">
                    <li><i class="bi bi-geo-alt"></i> {{ config('shop.address') }}</li>
                    <li><i class="bi bi-telephone"></i> <a href="tel:{{ preg_replace('/\D/', '', config('shop.phone')) }}">{{ config('shop.phone') }}</a></li>
                    <li><i class="bi bi-envelope"></i> <a href="mailto:{{ config('shop.email') }}">{{ config('shop.email') }}</a></li>
                    <li><i class="bi bi-clock"></i> {{ config('shop.opening_hours') }}</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            © {{ date('Y') }} {{ config('shop.name') }} · Đồ án môn Lập trình mã nguồn mở
        </div>
    </div>
</footer>
