<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    private Category $love;

    private Category $desk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->love = Category::create(['name' => 'Hoa tình yêu', 'slug' => 'hoa-tinh-yeu']);
        $this->desk = Category::create(['name' => 'Hoa để bàn', 'slug' => 'hoa-de-ban']);

        $this->make('Hồng đỏ Classic', $this->love, 650000);
        $this->make('Hồng kem Love Box', $this->love, 720000, 680000);
        $this->make('Lily để bàn', $this->desk, 550000);
        $this->make('Hoa sáp thơm', $this->desk, 250000, stock: 0);
        $this->make('Hoa đã ngừng bán', $this->desk, 100000, active: false);
    }

    private function make(string $name, Category $category, int $price, ?int $sale = null, int $stock = 5, bool $active = true): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'price' => $price,
            'sale_price' => $sale,
            'stock' => $stock,
            'is_active' => $active,
        ]);
    }

    private function names($response): array
    {
        return $response->viewData('products')->pluck('name')->all();
    }

    public function test_danh_sach_chi_hien_san_pham_dang_ban(): void
    {
        $response = $this->get(route('products.index'))->assertOk();

        $this->assertCount(4, $this->names($response));
        $this->assertNotContains('Hoa đã ngừng bán', $this->names($response));
    }

    public function test_tim_kiem_theo_ten(): void
    {
        $this->assertEqualsCanonicalizing(['Hồng đỏ Classic', 'Hồng kem Love Box'], $this->names($this->get(route('products.index', ['q' => 'Hồng']))));
        $this->assertSame([], $this->names($this->get(route('products.index', ['q' => 'không có']))));
    }

    public function test_loc_theo_danh_muc(): void
    {
        $names = $this->names($this->get(route('products.index', ['category' => 'hoa-de-ban'])));

        $this->assertEqualsCanonicalizing(['Lily để bàn', 'Hoa sáp thơm'], $names);
    }

    public function test_sap_xep_theo_gia_dung_gia_sau_khuyen_mai(): void
    {
        $this->assertSame(
            ['Hoa sáp thơm', 'Lily để bàn', 'Hồng đỏ Classic', 'Hồng kem Love Box'],
            $this->names($this->get(route('products.index', ['sort' => 'price_asc'])))
        );
        $this->assertSame(
            ['Hồng kem Love Box', 'Hồng đỏ Classic', 'Lily để bàn', 'Hoa sáp thơm'],
            $this->names($this->get(route('products.index', ['sort' => 'price_desc'])))
        );
    }

    public function test_phan_trang_giu_bo_loc(): void
    {
        foreach (range(1, 10) as $i) {
            $this->make("Hồng mẫu {$i}", $this->love, 300000 + $i);
        }

        $page1 = $this->get(route('products.index', ['category' => 'hoa-tinh-yeu']));
        $this->assertSame(12, $page1->viewData('products')->total());
        $this->assertCount(9, $this->names($page1));
        $page1->assertSee('category=hoa-tinh-yeu&amp;page=2', false);

        $this->assertCount(3, $this->names($this->get(route('products.index', ['category' => 'hoa-tinh-yeu', 'page' => 2]))));
    }

    public function test_chi_tiet_va_trang_thai_ton_kho(): void
    {
        $this->get(route('products.show', 'hong-kem-love-box'))
            ->assertOk()
            ->assertSee('680.000₫')
            ->assertSee('Còn hàng — còn 5 sản phẩm');

        $this->get(route('products.show', 'hoa-sap-thom'))
            ->assertOk()
            ->assertSee('Hết hàng')
            ->assertSee('Tạm hết hàng');

        $this->get(route('products.show', 'khong-ton-tai'))->assertNotFound();
        $this->get(route('products.show', 'hoa-da-ngung-ban'))->assertNotFound();
    }
}
