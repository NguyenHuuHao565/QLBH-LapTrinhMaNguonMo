<x-layout>
    {{-- Banner đầu trang: ảnh hoa thật phủ toàn chiều ngang --}}
    <section class="hero-banner" style="background-image: url('{{ asset('images/banner.jpg') }}')"
             role="img" aria-label="Những bó hoa tươi bày bán tại tiệm">
        <div class="container">
            <div class="hero-banner-content">
                <span class="eyebrow">Tiệm hoa Bloomy</span>
                <h1 class="hero-title">Hoa tươi mỗi ngày,<br>gửi trọn <em>yêu thương</em></h1>
                <p class="hero-lead">
                    {{ $productCount >= 10 ? "Hơn {$productCount} mẫu hoa" : 'Những mẫu hoa' }} được cắm thủ công từ hoa tươi tuyển chọn mỗi sáng —
                    cho sinh nhật, ngày kỷ niệm, khai trương, tiệc cưới hay đơn giản là một lời cảm ơn.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('products.index') }}" class="btn btn-bloom btn-lg">Khám phá hoa <i class="bi bi-arrow-right ms-1"></i></a>
                    <a href="#danh-muc" class="btn btn-light btn-lg rounded-pill px-4">Xem danh mục</a>
                </div>
            </div>
        </div>
    </section>

    <section class="hero-perks">
        <div class="container">
            <div class="row g-3 text-center text-md-start">
                <div class="col-6 col-md-3"><i class="bi bi-flower1"></i> <span>Hoa tươi trong ngày</span></div>
                <div class="col-6 col-md-3"><i class="bi bi-truck"></i> <span>Giao nội thành trong ngày</span></div>
                <div class="col-6 col-md-3"><i class="bi bi-cash-coin"></i> <span>Thanh toán khi nhận hàng</span></div>
                <div class="col-6 col-md-3"><i class="bi bi-envelope-heart"></i> <span>Tặng thiệp viết tay</span></div>
            </div>
        </div>
    </section>

    {{-- Danh mục --}}
    <section class="section" id="danh-muc">
        <div class="container">
            <div class="section-head">
                <div>
                    <span class="eyebrow">Danh mục</span>
                    <h2 class="section-title">Chọn hoa theo dịp</h2>
                </div>
            </div>

            <div class="row g-3 g-md-4 row-cols-2 row-cols-md-3 row-cols-xl-6">
                @foreach ($categories as $category)
                    <div class="col">
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="category-card">
                            <div class="category-art">
                                @if ($cover = $categoryCovers->get($category->id))
                                    <x-product-image :product="$cover" />
                                @else
                                    <x-flower-art :seed="$category->slug" :label="$category->name" />
                                @endif
                            </div>
                            <h3>{{ $category->name }}</h3>
                            <span>{{ $category->products_count }} mẫu hoa</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Sản phẩm nổi bật --}}
    @if ($featuredProducts->isNotEmpty())
        <section class="section section-tint">
            <div class="container">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Nổi bật</span>
                        <h2 class="section-title">Ưu đãi được yêu thích</h2>
                    </div>
                    <a href="{{ route('products.index') }}" class="link-more">Xem tất cả <i class="bi bi-arrow-right"></i></a>
                </div>

                <div class="row g-3 g-md-4 row-cols-2 row-cols-lg-4">
                    @foreach ($featuredProducts as $product)
                        <div class="col"><x-product-card :product="$product" /></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Sản phẩm mới --}}
    <section class="section">
        <div class="container">
            <div class="section-head">
                <div>
                    <span class="eyebrow">Mới về</span>
                    <h2 class="section-title">Mẫu hoa mới</h2>
                </div>
                <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="link-more">Xem tất cả <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="row g-3 g-md-4 row-cols-2 row-cols-md-3 row-cols-lg-4">
                @foreach ($newProducts as $product)
                    <div class="col"><x-product-card :product="$product" /></div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Cam kết --}}
    <section class="section section-tint">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-4 promise">
                    <i class="bi bi-flower2"></i>
                    <h3>Hoa tươi tuyển chọn</h3>
                    <p>Nhập hoa mới mỗi sáng, chỉ chọn những bông đẹp nhất.</p>
                </div>
                <div class="col-md-4 promise">
                    <i class="bi bi-truck"></i>
                    <h3>Giao trong ngày</h3>
                    <p>Giao hoa nội thành trong ngày, đóng gói cẩn thận.</p>
                </div>
                <div class="col-md-4 promise">
                    <i class="bi bi-envelope-heart"></i>
                    <h3>Thiệp viết tay</h3>
                    <p>Gửi kèm lời nhắn của bạn trên thiệp xinh xắn.</p>
                </div>
            </div>
        </div>
    </section>
</x-layout>
