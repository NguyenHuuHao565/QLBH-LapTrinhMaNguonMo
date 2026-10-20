@php($isEdit = $category->exists)

<x-admin-layout :title="$isEdit ? 'Sửa danh mục' : 'Thêm danh mục'">
    <x-slot:actions>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Danh sách</a>
    </x-slot:actions>

    <div class="card" style="max-width: 720px">
        <div class="card-body">
            <form action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="post" novalidate>
                @csrf
                @if ($isEdit) @method('PUT') @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" maxlength="100" required
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug (đường dẫn)</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $category->slug) }}" maxlength="120"
                           class="form-control @error('slug') is-invalid @enderror" placeholder="Để trống để tự sinh từ tên">
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea id="description" name="description" rows="3" maxlength="1000"
                              class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-check form-switch mb-4">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $category->is_active))>
                    <label class="form-check-label" for="is_active">Hiển thị trên cửa hàng</label>
                </div>

                <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> {{ $isEdit ? 'Lưu thay đổi' : 'Thêm danh mục' }}</button>
            </form>
        </div>
    </div>
</x-admin-layout>
