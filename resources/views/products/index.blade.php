<x-layout :title="$currentCategory?->name ?? 'Sản phẩm'">
    <section class="page-head">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    @if ($currentCategory)
                        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản phẩm</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $currentCategory->name }}</li>
                    @else
                        <li class="breadcrumb-item active" aria-current="page">Sản phẩm</li>
                    @endif
                </ol>
            </nav>
            <h1 class="page-title">{{ $currentCategory?->name ?? 'Tất cả sản phẩm' }}</h1>
            @if ($currentCategory?->description)
                <p class="page-lead">{{ $currentCategory->description }}</p>
            @endif
        </div>
    </section>

    <section class="section pt-4">
        <div class="container">
            <div class="row g-4">
                {{-- Danh mục --}}
                <aside class="col-lg-3">
                    <div class="filter-box">
                        <h2 class="filter-title">Danh mục</h2>
                        <ul class="category-filter">
                            <li>
                                <a href="{{ route('products.index', array_filter(['q' => $search, 'sort' => $sort === 'newest' ? null : $sort])) }}"
                                   @class(['active' => ! $currentCategory])>Tất cả</a>
                            </li>
                            @foreach ($categories as $category)
                                <li>
                                    <a href="{{ route('products.index', array_filter(['category' => $category->slug, 'q' => $search, 'sort' => $sort === 'newest' ? null : $sort])) }}"
                                       @class(['active' => $currentCategory?->is($category)])>{{ $category->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </aside>

                <div class="col-lg-9">
                    {{-- Tìm kiếm + sắp xếp --}}
                    <form class="toolbar" action="{{ route('products.index') }}" method="get">
                        @if ($currentCategory)
                            <input type="hidden" name="category" value="{{ $currentCategory->slug }}">
                        @endif
                        <div class="toolbar-search">
                            <i class="bi bi-search"></i>
                            <input type="search" name="q" value="{{ $search }}" class="form-control"
                                   placeholder="Tìm theo tên hoa..." aria-label="Tìm theo tên hoa" maxlength="100">
                        </div>
                        <select name="sort" class="form-select" aria-label="Sắp xếp" data-autosubmit>
                            @foreach ($sorts as $value => $label)
                                <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-bloom">Lọc</button>
                    </form>

                    <p class="result-count">
                        @if ($search !== '')
                            Tìm thấy <strong>{{ $products->total() }}</strong> sản phẩm cho “{{ $search }}”
                        @else
                            <strong>{{ $products->total() }}</strong> sản phẩm
                        @endif
                    </p>

                    @if ($products->isEmpty())
                        <div class="empty-state">
                            <i class="bi bi-flower3"></i>
                            <h2>Không tìm thấy sản phẩm phù hợp</h2>
                            <p>Hãy thử từ khóa khác hoặc xem toàn bộ cửa hàng.</p>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-bloom">Xóa bộ lọc</a>
                        </div>
                    @else
                        <div class="row g-3 g-md-4 row-cols-2 row-cols-md-3">
                            @foreach ($products as $product)
                                <div class="col"><x-product-card :product="$product" /></div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layout>
