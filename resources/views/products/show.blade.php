<x-layout :title="$product->name" :description="\Illuminate\Support\Str::limit($product->description, 150)">
    <section class="page-head page-head-sm">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản phẩm</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="section pt-3">
        <div class="container">
            <div class="row g-4 g-lg-5">
                <div class="col-md-6">
                    <div class="detail-media">
                        <x-product-image :product="$product" />
                        @if ($product->isOnSale())
                            <span class="badge-bloom sale">-{{ round(100 - $product->sale_price * 100 / $product->price) }}%</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="product-category">
                        {{ $product->category->name }}
                    </a>
                    <h1 class="detail-title">{{ $product->name }}</h1>

                    <x-price :product="$product" class="price-lg" />

                    <p class="stock {{ $product->isInStock() ? 'in' : 'out' }}">
                        @if ($product->isInStock())
                            <i class="bi bi-check-circle"></i> Còn hàng — còn {{ $product->stock }} sản phẩm
                        @else
                            <i class="bi bi-x-circle"></i> Hết hàng
                        @endif
                    </p>

                    <div class="detail-desc">{!! nl2br(e($product->description)) !!}</div>

                    {{-- Chỉ gửi số lượng; giá được tính lại ở server --}}
                    <form class="buy-box" action="{{ route('cart.add', $product) }}" method="post">
                        @csrf
                        <label for="quantity" class="form-label">Số lượng</label>
                        <div class="d-flex flex-wrap gap-3 align-items-center">
                            <div class="qty-stepper" data-qty>
                                <button type="button" data-qty-step="-1" aria-label="Giảm số lượng" @disabled(! $product->isInStock())><i class="bi bi-dash"></i></button>
                                <input type="number" id="quantity" name="quantity" value="1" min="1"
                                       max="{{ max($product->stock, 1) }}" inputmode="numeric" required @disabled(! $product->isInStock())>
                                <button type="button" data-qty-step="1" aria-label="Tăng số lượng" @disabled(! $product->isInStock())><i class="bi bi-plus"></i></button>
                            </div>
                            <button type="submit" class="btn btn-bloom btn-lg flex-grow-1" @disabled(! $product->isInStock())>
                                <i class="bi bi-bag-plus me-1"></i>
                                {{ $product->isInStock() ? 'Thêm vào giỏ hàng' : 'Tạm hết hàng' }}
                            </button>
                        </div>
                        @error('quantity') <p class="text-danger small mt-2 mb-0">{{ $message }}</p> @enderror
                    </form>

                    <ul class="detail-perks">
                        <li><i class="bi bi-flower2"></i> Hoa tươi tuyển chọn trong ngày</li>
                        <li><i class="bi bi-truck"></i> Giao nội thành trong ngày</li>
                        <li><i class="bi bi-envelope-heart"></i> Tặng kèm thiệp viết tay</li>
                    </ul>
                </div>
            </div>

            @if ($relatedProducts->isNotEmpty())
                <div class="section-head mt-5">
                    <div>
                        <span class="eyebrow">Có thể bạn thích</span>
                        <h2 class="section-title">Cùng danh mục {{ $product->category->name }}</h2>
                    </div>
                </div>
                <div class="row g-3 g-md-4 row-cols-2 row-cols-lg-4">
                    @foreach ($relatedProducts as $related)
                        <div class="col"><x-product-card :product="$related" /></div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-layout>
