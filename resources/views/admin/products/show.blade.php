<x-admin-layout :title="$product->name">
    <x-slot:actions>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Danh sách</a>
            @if ($product->is_active && $product->category->is_active)
                <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Xem trên cửa hàng</a>
            @endif
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Sửa</a>
        </div>
    </x-slot:actions>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card">
                <x-product-image :product="$product" class="admin-preview" />
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Danh mục</dt>
                        <dd class="col-sm-8">{{ $product->category->name }}</dd>

                        <dt class="col-sm-4">Slug</dt>
                        <dd class="col-sm-8"><code>{{ $product->slug }}</code></dd>

                        <dt class="col-sm-4">Giá bán</dt>
                        <dd class="col-sm-8">{{ number_format($product->price, 0, ',', '.') }}₫</dd>

                        <dt class="col-sm-4">Giá khuyến mãi</dt>
                        <dd class="col-sm-8">{{ $product->sale_price ? number_format($product->sale_price, 0, ',', '.').'₫' : '—' }}</dd>

                        <dt class="col-sm-4">Tồn kho</dt>
                        <dd class="col-sm-8">{{ $product->stock }} {!! $product->isInStock() ? '' : '<span class="badge text-bg-warning">Hết hàng</span>' !!}</dd>

                        <dt class="col-sm-4">Trạng thái</dt>
                        <dd class="col-sm-8">
                            @if ($product->is_active)
                                <span class="badge text-bg-success">Đang bán</span>
                            @else
                                <span class="badge text-bg-secondary">Ngừng kinh doanh</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Số dòng trong đơn hàng</dt>
                        <dd class="col-sm-8">{{ $product->order_items_count }}</dd>

                        <dt class="col-sm-4">Mô tả</dt>
                        <dd class="col-sm-8">{!! nl2br(e($product->description)) ?: '—' !!}</dd>
                    </dl>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <form action="{{ route('admin.products.toggle-active', $product) }}" method="post"
                      data-confirm="{{ $product->is_active ? 'Ngừng kinh doanh' : 'Mở bán lại' }} “{{ $product->name }}”?">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn {{ $product->is_active ? 'btn-warning' : 'btn-success' }}">
                        <i class="bi {{ $product->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                        {{ $product->is_active ? 'Ngừng kinh doanh' : 'Mở bán lại' }}
                    </button>
                </form>

                @if ($product->order_items_count === 0)
                    <form action="{{ route('admin.products.destroy', $product) }}" method="post"
                          data-confirm="Xóa vĩnh viễn “{{ $product->name }}”? Thao tác không thể hoàn tác.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i> Xóa sản phẩm</button>
                    </form>
                @else
                    <span class="text-muted small align-self-center">Sản phẩm đã có trong đơn hàng nên chỉ có thể ngừng kinh doanh, không xóa.</span>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
