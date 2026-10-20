<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $this->searchTerm($request);

        $categories = Category::query()
            ->withCount('products')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category(['is_active' => true])]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());

        return redirect()->route('admin.categories.index')->with('status', "Đã thêm danh mục “{$category->name}”.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('status', "Đã cập nhật danh mục “{$category->name}”.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Không xóa danh mục còn sản phẩm (khóa ngoại RESTRICT); hãy ẩn danh mục thay vì xóa
        if ($category->products()->exists()) {
            return back()->with('error', "Không thể xóa “{$category->name}” vì còn sản phẩm. Hãy tắt trạng thái hiển thị thay vì xóa.");
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', "Đã xóa danh mục “{$category->name}”.");
    }
}
