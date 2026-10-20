<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public const SORTS = [
        'newest' => 'Mới nhất',
        'price_asc' => 'Giá tăng dần',
        'price_desc' => 'Giá giảm dần',
        'name' => 'Tên A → Z',
    ];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim($filters['q'] ?? '');
        // Giá trị sort không hợp lệ thì dùng mặc định thay vì báo lỗi
        $sort = $request->query('sort');
        $sort = is_string($sort) && array_key_exists($sort, self::SORTS) ? $sort : 'newest';

        $categories = Category::active()->orderBy('id')->get();
        $currentCategory = isset($filters['category'])
            ? $categories->firstWhere('slug', $filters['category'])
            : null;

        $products = Product::visible()
            ->with('category')
            ->when($currentCategory, fn ($q) => $q->where('category_id', $currentCategory->id))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $q->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('description', 'like', $like));
            })
            ->when($sort === 'price_asc', fn ($q) => $q->orderByRaw('COALESCE(sale_price, price) asc'))
            ->when($sort === 'price_desc', fn ($q) => $q->orderByRaw('COALESCE(sale_price, price) desc'))
            ->when($sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when($sort === 'newest', fn ($q) => $q->latest())
            ->orderBy('id')
            ->paginate(9)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $currentCategory,
            'search' => $search,
            'sort' => $sort,
            'sorts' => self::SORTS,
        ]);
    }

    public function show(Product $product): View
    {
        // Sản phẩm ngừng kinh doanh hoặc thuộc danh mục bị ẩn: trả về 404
        abort_unless(
            $product->is_active && $product->category?->is_active,
            404
        );

        $relatedProducts = Product::visible()
            ->with('category')
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }
}
