<x-admin-layout title="Sản phẩm">
    <x-slot:actions>
        <a href="{{ route('admin.products.create') }}" class="btn btn-success"><i class="bi bi-plus-lg"></i> Thêm sản phẩm</a>
    </x-slot:actions>

    <form class="row g-2 mb-3" action="{{ route('admin.products.index') }}" method="get" role="search">
        <div class="col-md-5">
            <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Tìm theo tên sản phẩm..." maxlength="100" aria-label="Tìm sản phẩm">
        </div>
        <div class="col-sm-6 col-md-3">
            <select name="category" class="form-select" aria-label="Lọc danh mục">
                <option value="">Tất cả danh mục</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-md-2">
            <select name="status" class="form-select" aria-label="Lọc trạng thái">
                <option value="">Mọi trạng thái</option>
                <option value="active" @selected($status === 'active')>Đang bán</option>
                <option value="inactive" @selected($status === 'inactive')>Ngừng kinh doanh</option>
                <option value="out" @selected($status === 'out')>Hết hàng</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-outline-secondary flex-grow-1"><i class="bi bi-funnel"></i> Lọc</button>
            @if ($search !== '' || $categoryId || $status)
                <a href="{{ route('admin.products.index') }}" class="btn btn-link" title="Xóa lọc"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>

    <p class="text-muted small">{{ $products->total() }} sản phẩm</p>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 64px">Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th class="text-end">Giá bán</th>
                        <th class="text-center">Tồn kho</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr @class(['table-secondary' => ! $product->is_active])>
                            <td><x-product-image :product="$product" class="admin-thumb" /></td>
                            <td>
                                <a href="{{ route('admin.products.show', $product) }}" class="fw-semibold text-decoration-none">{{ $product->name }}</a>
                                <div class="small text-muted"><code>{{ $product->slug }}</code></div>
                            </td>
                            <td>{{ $product->category->name }}</td>
                            <td class="text-end text-nowrap">
                                {{ number_format($product->finalPrice(), 0, ',', '.') }}₫
                                @if ($product->isOnSale())
                                    <div class="small text-muted text-decoration-line-through">{{ number_format($product->price, 0, ',', '.') }}₫</div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($product->isInStock())
                                    {{ $product->stock }}
                                @else
                                    <span class="badge text-bg-warning">Hết hàng</span>
                                @endif
                            </td>
                            <td>
                                @if ($product->is_active)
                                    <span class="badge text-bg-success">Đang bán</span>
                                @else
                                    <span class="badge text-bg-secondary">Ngừng KD</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary" title="Sửa"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('admin.products.toggle-active', $product) }}" method="post" class="d-inline"
                                      data-confirm="{{ $product->is_active ? 'Ngừng kinh doanh' : 'Mở bán lại' }} “{{ $product->name }}”?">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $product->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            title="{{ $product->is_active ? 'Ngừng kinh doanh' : 'Mở bán lại' }}">
                                        <i class="bi {{ $product->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Không có sản phẩm phù hợp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $products->links() }}</div>
</x-admin-layout>
