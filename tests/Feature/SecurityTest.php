<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderPlacer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private const XSS = '<script>alert("xss")</script><img src=x onerror=alert(1)>';

    private User $admin;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->forceFill(['role' => User::ROLE_ADMIN])->save();
        $this->customer = User::factory()->create(['name' => 'Khách '.self::XSS]);
    }

    public function test_tham_so_dang_mang_khong_gay_loi_500(): void
    {
        Category::create(['name' => 'Hoa', 'slug' => 'hoa']);
        $query = '?q[]=x&sort[]=x&category[]=x&status[]=x&from[]=x&to[]=x&page[]=x';

        foreach (['/', '/san-pham', '/gio-hang', '/dang-nhap', '/dang-ky'] as $url) {
            $this->assertLessThan(500, $this->get($url.$query)->status(), "Khách: {$url}");
        }
        foreach (['/', '/tai-khoan', '/don-hang', '/gio-hang'] as $url) {
            $this->assertLessThan(500, $this->actingAs($this->customer)->get($url.$query)->status(), "Customer: {$url}");
        }
        foreach (['/admin', '/admin/orders', '/admin/customers', '/admin/products', '/admin/categories'] as $url) {
            $this->assertLessThan(500, $this->actingAs($this->admin)->get($url.$query)->status(), "Admin: {$url}");
        }
    }

    public function test_du_lieu_sai_ma_utf8_bi_tu_choi_400(): void
    {
        $this->post(route('register.store'), [
            'name' => "\xC3\x28", 'email' => 'x@example.com', 'password' => 'matkhau123', 'password_confirmation' => 'matkhau123',
        ])->assertStatus(400);

        $this->get('/san-pham?q='.urlencode("\xC3\x28"))->assertStatus(400);
        $this->assertSame(2, User::count());

        // Tiếng Việt UTF-8 hợp lệ vẫn bình thường
        $this->get('/san-pham?q='.urlencode('hồng'))->assertOk();
    }

    public function test_xss_luu_tru_duoc_escape_o_moi_noi_hien_thi(): void
    {
        $category = Category::create(['name' => 'DM '.self::XSS, 'slug' => 'dm-xss', 'description' => self::XSS]);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'SP '.self::XSS, 'slug' => 'sp-xss',
            'description' => "Dòng 1\n".self::XSS, 'price' => 100000, 'stock' => 5,
        ]);
        $order = app(OrderPlacer::class)->place($this->customer, [$product->id => 1], [
            'recipient_name' => 'NN '.self::XSS, 'recipient_phone' => '0900000000',
            'shipping_address' => 'DC '.self::XSS, 'note' => "Ghi chú\n".self::XSS,
        ], (string) Str::uuid());

        $pages = [
            [null, route('home')],
            [null, route('products.index')],
            [null, route('products.index', ['category' => 'dm-xss'])],
            [null, route('products.show', $product)],
            [null, route('products.index', ['q' => self::XSS])],
            [$this->customer, route('account.show')],
            [$this->customer, route('orders.show', $order)],
            [$this->customer, route('checkout.success', $order)],
            [$this->admin, route('admin.dashboard')],
            [$this->admin, route('admin.orders.index')],
            [$this->admin, route('admin.orders.show', $order)],
            [$this->admin, route('admin.customers.index')],
            [$this->admin, route('admin.customers.show', $this->customer)],
            [$this->admin, route('admin.products.index')],
            [$this->admin, route('admin.products.show', $product)],
            [$this->admin, route('admin.products.edit', $product)],
            [$this->admin, route('admin.categories.index')],
        ];

        foreach ($pages as [$user, $url]) {
            $response = $user ? $this->actingAs($user)->get($url) : $this->get($url);
            $response->assertOk();
            $html = $response->getContent();

            $this->assertStringNotContainsString('<script>alert("xss")', $html, "Script chưa escape tại {$url}");
            $this->assertStringNotContainsString('<img src=x onerror', $html, "Thẻ img chưa escape tại {$url}");
            auth()->logout();
        }

        // Nội dung vẫn hiển thị (dạng văn bản), không bị nuốt mất
        $this->get(route('products.show', $product))->assertSee('&lt;script&gt;', false);
    }
}
