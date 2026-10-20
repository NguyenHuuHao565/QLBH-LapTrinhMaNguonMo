<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create();
        $this->admin->forceFill(['role' => User::ROLE_ADMIN])->save();

        $this->category = Category::create(['name' => 'Hoa sinh nhật', 'slug' => 'hoa-sinh-nhat']);
    }

    private function product(array $attributes = []): Product
    {
        static $n = 0;
        $n++;

        return Product::create(array_merge([
            'category_id' => $this->category->id,
            'name' => "Hoa thử {$n}",
            'slug' => "hoa-thu-{$n}",
            'price' => 300000,
            'stock' => 10,
        ], $attributes));
    }

    private function productPayload(array $overrides = []): array
    {
        return array_merge([
            'category_id' => $this->category->id,
            'name' => 'Bó hồng Pastel',
            'slug' => '',
            'description' => 'Bó hồng pastel nhẹ nhàng.',
            'price' => 500000,
            'sale_price' => 450000,
            'stock' => 12,
            'is_active' => '1',
        ], $overrides);
    }

    // ---------- Phân quyền ----------

    public function test_customer_va_khach_khong_vao_duoc_route_quan_tri(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();

        $this->get(route('admin.products.index'))->assertRedirect(route('login'));
        $this->actingAs($customer)->get(route('admin.products.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.categories.index'))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.products.store'), $this->productPayload())->assertForbidden();
        $this->actingAs($customer)->patch(route('admin.products.toggle-active', $product))->assertForbidden();
        $this->actingAs($customer)->delete(route('admin.categories.destroy', $this->category))->assertForbidden();

        $this->assertSame(1, Product::count());
        $this->assertTrue($product->fresh()->is_active);
    }

    // ---------- Danh mục ----------

    public function test_them_danh_muc_tu_sinh_slug(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), ['name' => 'Hoa cưới', 'description' => 'Hoa cầm tay cô dâu', 'is_active' => '1'])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('categories', ['name' => 'Hoa cưới', 'slug' => 'hoa-cuoi', 'is_active' => true]);
    }

    public function test_danh_muc_validation_ten_trong_va_trung(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), ['name' => 'Hoa sinh nhật'])
            ->assertSessionHasErrors(['name', 'slug']);

        $this->assertSame(1, Category::count());
    }

    public function test_sua_va_an_danh_muc(): void
    {
        $product = $this->product();

        $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $this->category), ['name' => 'Hoa sinh nhật đẹp', 'slug' => 'hoa-sinh-nhat'])
            ->assertRedirect(route('admin.categories.index'));

        $this->category->refresh();
        $this->assertSame('Hoa sinh nhật đẹp', $this->category->name);
        $this->assertFalse($this->category->is_active); // không gửi is_active => tắt

        // Danh mục ẩn thì sản phẩm thuộc danh mục cũng không hiện ở cửa hàng
        $this->get(route('products.show', $product))->assertNotFound();
    }

    public function test_xoa_danh_muc(): void
    {
        $empty = Category::create(['name' => 'Trống', 'slug' => 'trong']);
        $this->product();

        $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $empty))->assertRedirect();
        $this->assertModelMissing($empty);

        // Còn sản phẩm thì không xóa được
        $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $this->category))->assertSessionHas('error');
        $this->assertModelExists($this->category);
    }

    public function test_tim_kiem_va_phan_trang_danh_muc(): void
    {
        foreach (range(1, 12) as $i) {
            Category::create(['name' => "Nhóm {$i}", 'slug' => "nhom-{$i}"]);
        }

        $this->actingAs($this->admin)->get(route('admin.categories.index'))
            ->assertOk()->assertViewHas('categories', fn ($p) => $p->total() === 13 && $p->count() === 10);

        $this->actingAs($this->admin)->get(route('admin.categories.index', ['page' => 2]))
            ->assertViewHas('categories', fn ($p) => $p->count() === 3);

        $this->actingAs($this->admin)->get(route('admin.categories.index', ['q' => 'sinh nhật']))
            ->assertViewHas('categories', fn ($p) => $p->total() === 1)
            ->assertSee('Hoa sinh nhật');
    }

    // ---------- Sản phẩm ----------

    public function test_them_san_pham_co_anh(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->productPayload(['image' => UploadedFile::fake()->image('Ảnh gốc của tôi.jpg', 600, 600)]))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHasNoErrors();

        $product = Product::where('slug', 'bo-hong-pastel')->firstOrFail();

        $this->assertSame(450000, $product->sale_price);
        $this->assertStringStartsWith('products/', $product->image);
        $this->assertStringNotContainsString('Ảnh gốc', $product->image); // tên file do Laravel sinh
        $this->assertStringEndsWith('.jpg', $product->image);
        Storage::disk('public')->assertExists($product->image);

        // Cửa hàng hiển thị ảnh thật thay cho minh họa
        $this->get(route('products.show', $product))->assertOk()->assertSee('storage/'.$product->image);
    }

    public function test_them_san_pham_khong_anh_dung_placeholder(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->productPayload());

        $product = Product::firstOrFail();
        $this->assertNull($product->image);
        $this->get(route('products.show', $product))->assertOk()->assertSee('class="flower-art"', false);
    }

    public function test_validation_san_pham(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->productPayload([
                'name' => '',
                'category_id' => 9999,      // danh mục không tồn tại
                'price' => -5,
                'sale_price' => 900000,     // lớn hơn giá bán
                'stock' => 'abc',
            ]))
            ->assertSessionHasErrors(['name', 'category_id', 'price', 'stock']);

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->productPayload(['sale_price' => 600000]))
            ->assertSessionHasErrors(['sale_price' => 'Giá khuyến mãi phải nhỏ hơn giá bán.']);

        $this->product(['slug' => 'trung-slug']);
        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->productPayload(['slug' => 'trung-slug']))
            ->assertSessionHasErrors('slug');

        $this->assertSame(1, Product::count());
    }

    public function test_tu_choi_file_khong_hop_le(): void
    {
        $badFiles = [
            'file php' => UploadedFile::fake()->create('shell.php', 5, 'application/x-php'),
            'file exe' => UploadedFile::fake()->create('virus.exe', 5, 'application/x-msdownload'),
            'svg' => UploadedFile::fake()->create('logo.svg', 5, 'image/svg+xml'),
            'pdf' => UploadedFile::fake()->create('menu.pdf', 5, 'application/pdf'),
            'qua 2MB' => UploadedFile::fake()->image('big.jpg', 800, 800)->size(3000),
            'qua nho' => UploadedFile::fake()->image('tiny.png', 50, 50),
        ];

        foreach ($badFiles as $label => $file) {
            $this->actingAs($this->admin)
                ->post(route('admin.products.store'), $this->productPayload(['image' => $file]))
                ->assertSessionHasErrors('image', "Không chặn được: {$label}");
        }

        $this->assertSame(0, Product::count());
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_xem_va_sua_san_pham_thay_anh_xoa_anh_cu(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->productPayload(['image' => UploadedFile::fake()->image('a.png', 400, 400)]));
        $product = Product::firstOrFail();
        $oldImage = $product->image;

        $this->actingAs($this->admin)->get(route('admin.products.show', $product))->assertOk()->assertSee('Bó hồng Pastel');
        $this->actingAs($this->admin)->get(route('admin.products.edit', $product))->assertOk()->assertSee('value="500000"', false);

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), $this->productPayload([
                'name' => 'Bó hồng Pastel Deluxe',
                'slug' => 'bo-hong-pastel',
                'price' => 650000,
                'sale_price' => '',
                'stock' => 3,
                'image' => UploadedFile::fake()->image('b.webp', 500, 500),
            ]))
            ->assertRedirect(route('admin.products.show', $product))
            ->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertSame('Bó hồng Pastel Deluxe', $product->name);
        $this->assertSame(650000, $product->price);
        $this->assertNull($product->sale_price);
        $this->assertSame(3, $product->stock);
        $this->assertNotSame($oldImage, $product->image);
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($product->image);

        // Gỡ ảnh
        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), $this->productPayload(['slug' => 'bo-hong-pastel', 'remove_image' => '1']));
        $this->assertNull($product->fresh()->image);
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_san_pham_hoac_danh_muc_khong_ton_tai_tra_404(): void
    {
        $this->actingAs($this->admin)->get(route('admin.products.edit', 9999))->assertNotFound();
        $this->actingAs($this->admin)->put(route('admin.products.update', 9999), $this->productPayload())->assertNotFound();
        $this->actingAs($this->admin)->get(route('admin.categories.edit', 9999))->assertNotFound();
    }

    public function test_ngung_kinh_doanh_va_mo_ban_lai(): void
    {
        $product = $this->product(['name' => 'Hoa ngừng bán']);

        $this->actingAs($this->admin)->patch(route('admin.products.toggle-active', $product))->assertSessionHas('status');
        $this->assertFalse($product->fresh()->is_active);

        $this->get(route('products.show', $product))->assertNotFound();
        $this->get(route('products.index'))->assertDontSee('Hoa ngừng bán');

        $this->actingAs($this->admin)->patch(route('admin.products.toggle-active', $product));
        $this->assertTrue($product->fresh()->is_active);
        $this->get(route('products.show', $product))->assertOk();
    }

    public function test_khong_xoa_cung_san_pham_da_co_trong_don(): void
    {
        $sold = $this->product(['name' => 'Hoa đã bán']);
        $order = Order::create(['recipient_name' => 'An', 'recipient_phone' => '0901234567', 'shipping_address' => 'HCM']);
        $order->items()->save(OrderItem::fromProduct($sold, 1));

        $this->actingAs($this->admin)->delete(route('admin.products.destroy', $sold))->assertSessionHas('error');
        $this->assertModelExists($sold);
        $this->assertSame('Hoa đã bán', $order->items()->first()->product_name);

        $unsold = $this->product();
        $this->actingAs($this->admin)->delete(route('admin.products.destroy', $unsold))->assertRedirect(route('admin.products.index'));
        $this->assertModelMissing($unsold);
    }

    public function test_tim_kiem_loc_va_phan_trang_san_pham_quan_tri(): void
    {
        foreach (range(1, 11) as $i) {
            $this->product();
        }
        $other = Category::create(['name' => 'Hoa để bàn', 'slug' => 'hoa-de-ban']);
        $this->product(['name' => 'Lan hồ điệp trắng', 'category_id' => $other->id, 'stock' => 0, 'is_active' => false]);

        $this->actingAs($this->admin)->get(route('admin.products.index'))
            ->assertOk()->assertViewHas('products', fn ($p) => $p->total() === 12 && $p->count() === 10);
        $this->actingAs($this->admin)->get(route('admin.products.index', ['page' => 2]))
            ->assertViewHas('products', fn ($p) => $p->count() === 2);
        $this->actingAs($this->admin)->get(route('admin.products.index', ['q' => 'hồ điệp']))
            ->assertViewHas('products', fn ($p) => $p->total() === 1);
        $this->actingAs($this->admin)->get(route('admin.products.index', ['category' => $other->id]))
            ->assertViewHas('products', fn ($p) => $p->total() === 1);
        $this->actingAs($this->admin)->get(route('admin.products.index', ['status' => 'inactive']))
            ->assertViewHas('products', fn ($p) => $p->total() === 1);
        $this->actingAs($this->admin)->get(route('admin.products.index', ['status' => 'out']))
            ->assertViewHas('products', fn ($p) => $p->total() === 1);
        // Ký tự đại diện của LIKE được coi là chữ thường, không khớp tất cả
        $this->actingAs($this->admin)->get(route('admin.products.index', ['q' => '%']))
            ->assertViewHas('products', fn ($p) => $p->total() === 0);
    }
}
