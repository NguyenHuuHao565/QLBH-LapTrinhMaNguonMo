<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = $this->searchTerm($request);
        $categoryId = $request->integer('category') ?: null;
        $status = in_array($request->query('status'), ['active', 'inactive', 'out'], true) ? $request->query('status') : null;

        $products = Product::query()
            ->with('category')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($status === 'out', fn ($q) => $q->where('stock', 0))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('id')->get(),
            'search' => $search,
            'categoryId' => $categoryId,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(['is_active' => true, 'stock' => 0]),
            'categories' => Category::orderBy('id')->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request);
        }

        $product = Product::create($data);

        return redirect()->route('admin.products.index')->with('status', "Đã thêm sản phẩm “{$product->name}”.");
    }

    public function show(Product $product): View
    {
        $product->load('category')->loadCount('orderItems');

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('id')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);
        $oldImage = $product->image;

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request);
        } elseif ($request->validated('remove_image')) {
            $data['image'] = null;
        }

        $product->update($data);

        // Xóa file ảnh cũ sau khi đã lưu thành công ảnh mới / gỡ ảnh
        if (array_key_exists('image', $data) && $oldImage && $oldImage !== $product->image) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('admin.products.show', $product)->with('status', "Đã cập nhật sản phẩm “{$product->name}”.");
    }

    /**
     * Ngừng / mở lại kinh doanh: ẩn khỏi cửa hàng nhưng giữ nguyên dữ liệu và lịch sử đơn.
     */
    public function toggleActive(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        $message = $product->is_active ? 'Đã mở bán lại' : 'Đã ngừng kinh doanh';

        return back()->with('status', "{$message} “{$product->name}”.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Sản phẩm đã có trong đơn hàng thì không xóa cứng, chỉ cho ngừng kinh doanh
        if ($product->orderItems()->exists()) {
            return back()->with('error', "“{$product->name}” đã có trong đơn hàng nên không thể xóa. Hãy dùng chức năng ngừng kinh doanh.");
        }

        $product->delete();

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        return redirect()->route('admin.products.index')->with('status', "Đã xóa sản phẩm “{$product->name}”.");
    }

    /**
     * Lưu ảnh vào storage/app/public/products với tên ngẫu nhiên do Laravel sinh
     * (không dùng tên file người dùng gửi lên), đuôi file lấy theo MIME thực tế.
     */
    private function storeImage(ProductRequest $request): string
    {
        return $request->file('image')->store('products', 'public');
    }
}
