@php($isEdit = $product->exists)

<x-admin-layout :title="$isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm'">
    <x-slot:actions>
        <a href="{{ $isEdit ? route('admin.products.show', $product) : route('admin.products.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
    </x-slot:actions>

    <form action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}" method="post"
          enctype="multipart/form-data" novalidate>
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" maxlength="150" required
                                   class="form-control @error('name') is-invalid @enderror">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                <select id="category_id" name="category_id" required class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">— Chọn danh mục —</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>
                                            {{ $category->name }}{{ $category->is_active ? '' : ' (đang ẩn)' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="slug" class="form-label">Slug (đường dẫn)</label>
                                <input type="text" id="slug" name="slug" value="{{ old('slug', $product->slug) }}" maxlength="180"
                                       class="form-control @error('slug') is-invalid @enderror" placeholder="Để trống để tự sinh từ tên">
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="price" class="form-label">Giá bán (₫) <span class="text-danger">*</span></label>
                                <input type="number" id="price" name="price" value="{{ old('price', $product->price) }}" min="1000" step="1000" required
                                       class="form-control @error('price') is-invalid @enderror">
                                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="sale_price" class="form-label">Giá khuyến mãi (₫)</label>
                                <input type="number" id="sale_price" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" min="1000" step="1000"
                                       class="form-control @error('sale_price') is-invalid @enderror" placeholder="Không bắt buộc">
                                @error('sale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="stock" class="form-label">Tồn kho <span class="text-danger">*</span></label>
                                <input type="number" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" min="0" required
                                       class="form-control @error('stock') is-invalid @enderror">
                                @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-0">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea id="description" name="description" rows="6" maxlength="5000"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <label for="image" class="form-label">Ảnh sản phẩm</label>
                        <x-product-image :product="$product" class="admin-preview mb-3" />
                        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
                               class="form-control @error('image') is-invalid @enderror">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">JPG, PNG hoặc WEBP · tối đa 2 MB · tối thiểu 200×200 px.</div>
                        @enderror

                        @if ($isEdit && $product->image)
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                <label class="form-check-label" for="remove_image">Gỡ ảnh hiện tại</label>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                                   @checked(old('is_active', $product->is_active))>
                            <label class="form-check-label" for="is_active">Đang kinh doanh</label>
                        </div>
                        <div class="form-text">Tắt để ngừng bán: sản phẩm bị ẩn khỏi cửa hàng nhưng vẫn giữ lịch sử đơn hàng.</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100"><i class="bi bi-check-lg"></i> {{ $isEdit ? 'Lưu thay đổi' : 'Thêm sản phẩm' }}</button>
            </div>
        </div>
    </form>
</x-admin-layout>
