<x-admin-layout title="Danh mục">
    <x-slot:actions>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-success"><i class="bi bi-plus-lg"></i> Thêm danh mục</a>
    </x-slot:actions>

    <form class="row g-2 mb-3" action="{{ route('admin.categories.index') }}" method="get" role="search">
        <div class="col-sm-8 col-md-6">
            <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Tìm theo tên danh mục..." maxlength="100" aria-label="Tìm danh mục">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i> Tìm</button>
            @if ($search !== '')
                <a href="{{ route('admin.categories.index') }}" class="btn btn-link">Xóa lọc</a>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tên danh mục</th>
                        <th>Slug</th>
                        <th class="text-center">Sản phẩm</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="text-muted">{{ $category->id }}</td>
                            <td class="fw-semibold">{{ $category->name }}</td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td class="text-center">{{ $category->products_count }}</td>
                            <td>
                                @if ($category->is_active)
                                    <span class="badge text-bg-success">Hiển thị</span>
                                @else
                                    <span class="badge text-bg-secondary">Đang ẩn</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Sửa</a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="post" class="d-inline"
                                      data-confirm="Xóa danh mục “{{ $category->name }}”?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" @disabled($category->products_count > 0)
                                            title="{{ $category->products_count > 0 ? 'Còn sản phẩm, không thể xóa' : 'Xóa' }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Không có danh mục phù hợp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $categories->links() }}</div>
</x-admin-layout>
