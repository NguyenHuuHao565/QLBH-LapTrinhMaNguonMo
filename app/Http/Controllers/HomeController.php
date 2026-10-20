<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::active()
            ->withCount(['products' => fn ($q) => $q->active()])
            ->orderBy('id')
            ->get();

        // Ảnh đại diện cho mỗi danh mục: sản phẩm đầu tiên có ảnh trong danh mục đó
        $categoryCovers = Product::visible()
            ->whereNotNull('image')
            ->orderBy('id')
            ->get(['id', 'category_id', 'name', 'slug', 'image'])
            ->unique('category_id')
            ->keyBy('category_id');

        // Nổi bật: sản phẩm còn hàng đang có giá ưu đãi
        $featuredProducts = Product::visible()
            ->with('category')
            ->whereNotNull('sale_price')
            ->where('stock', '>', 0)
            ->latest('updated_at')
            ->take(4)
            ->get();

        $newProducts = Product::visible()
            ->with('category')
            ->latest()
            ->latest('id')
            ->take(8)
            ->get();

        $productCount = (int) (floor(Product::visible()->count() / 10) * 10); // "Hơn 50 mẫu hoa"

        return view('home', compact('categories', 'categoryCovers', 'featuredProducts', 'newProducts', 'productCount'));
    }
}
