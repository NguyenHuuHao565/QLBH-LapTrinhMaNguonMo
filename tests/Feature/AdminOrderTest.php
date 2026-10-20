<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderPlacer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customer;

    private Product $rose;

    private Product $lily;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['name' => 'Quản trị']);
        $this->admin->forceFill(['role' => User::ROLE_ADMIN])->save();
        $this->customer = User::factory()->create(['name' => 'Khách A', 'email' => 'a@example.com', 'phone' => '0911111111']);

        $category = Category::create(['name' => 'Hoa tình yêu', 'slug' => 'hoa-tinh-yeu']);
        $this->rose = Product::create(['category_id' => $category->id, 'name' => 'Hồng đỏ', 'slug' => 'hong-do', 'price' => 500000, 'stock' => 50]);
        $this->lily = Product::create(['category_id' => $category->id, 'name' => 'Lily', 'slug' => 'lily', 'price' => 200000, 'stock' => 50]);
    }

    /**
     * Tạo đơn bằng chính service checkout (không chèn dữ liệu giả vào bảng orders).
     */
    private function placeOrder(array $items, ?User $user = null, ?string $status = null): Order
    {
        $order = app(OrderPlacer::class)->place($user ?? $this->customer, $items, [
            'recipient_name' => 'Người nhận', 'recipient_phone' => '0900000000', 'shipping_address' => 'HCM',
        ], (string) Str::uuid());

        if ($status) {
            $order->forceFill(['status' => $status])->save();
        }

        return $order->fresh();
    }

    private function transition(Order $order, string $to, ?string $from = null)
    {
        return $this->actingAs($this->admin)->patch(route('admin.orders.update-status', $order), [
            'status' => $to,
            'from' => $from ?? $order->status,
        ]);
    }

    // ---------- Phân quyền ----------

    public function test_customer_va_khach_khong_vao_duoc_admin(): void
    {
        $order = $this->placeOrder([$this->rose->id => 1]);

        $routes = [
            ['get', route('admin.dashboard')],
            ['get', route('admin.orders.index')],
            ['get', route('admin.orders.show', $order)],
            ['patch', route('admin.orders.update-status', $order)],
            ['get', route('admin.customers.index')],
            ['get', route('admin.customers.show', $this->customer)],
        ];

        foreach ($routes as [$method, $url]) {
            $this->actingAs($this->customer)->{$method}($url, ['status' => 'cancelled', 'from' => 'pending'])->assertForbidden();
        }

        auth()->logout();
        foreach ($routes as [$method, $url]) {
            $this->{$method}($url, ['status' => 'cancelled', 'from' => 'pending'])->assertRedirect(route('login'));
        }

        $this->assertSame('pending', $order->fresh()->status);
    }

    // ---------- Dashboard ----------

    public function test_dashboard_phan_anh_du_lieu_that_va_loai_don_huy(): void
    {
        $this->placeOrder([$this->rose->id => 2], status: 'completed');                         // 1.000.000
        $this->placeOrder([$this->lily->id => 1], status: 'completed');                         //   200.000
        $this->placeOrder([$this->rose->id => 10], status: 'cancelled');                        // 5.000.000 (không tính)
        $this->placeOrder([$this->lily->id => 3]);                                              //   600.000 pending
        $latest = $this->placeOrder([$this->rose->id => 1, $this->lily->id => 1], status: 'shipping'); // 700.000
        User::factory()->create(); // khách thứ 2, chưa mua

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();
        $stats = $response->viewData('stats');

        $this->assertSame(1200000, $stats['revenue']);   // chỉ đơn completed
        $this->assertSame(1300000, $stats['pipeline']);  // pending + shipping
        $this->assertSame(5, $stats['orders']);
        $this->assertSame(2, $stats['customers']);       // không đếm admin
        $this->assertSame(2, $stats['products']);
        $this->assertSame((int) Order::where('status', 'completed')->sum('total'), $stats['revenue']);

        $response->assertSee('1.200.000₫')->assertSee($latest->code);
        $this->assertSame($latest->id, $response->viewData('latestOrders')->first()->id);
    }

    public function test_dashboard_khi_chua_co_don(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()->assertSee('Chưa có đơn hàng nào.')
            ->assertViewHas('stats', fn ($s) => $s['revenue'] === 0 && $s['orders'] === 0);
    }

    // ---------- Danh sách & chi tiết đơn ----------

    public function test_tim_kiem_loc_va_phan_trang_don(): void
    {
        $target = $this->placeOrder([$this->rose->id => 1]);
        $old = $this->placeOrder([$this->lily->id => 1], status: 'completed');
        $old->forceFill(['placed_at' => now()->subDays(10)])->save();
        foreach (range(1, 15) as $i) {
            $this->placeOrder([$this->lily->id => 1]);
        }

        $this->actingAs($this->admin)->get(route('admin.orders.index'))
            ->assertOk()->assertViewHas('orders', fn ($p) => $p->total() === 17 && $p->count() === 15);
        $this->actingAs($this->admin)->get(route('admin.orders.index', ['page' => 2]))
            ->assertViewHas('orders', fn ($p) => $p->count() === 2);

        $this->actingAs($this->admin)->get(route('admin.orders.index', ['q' => $target->code]))
            ->assertViewHas('orders', fn ($p) => $p->total() === 1 && $p->first()->is($target));

        $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => 'completed']))
            ->assertViewHas('orders', fn ($p) => $p->total() === 1 && $p->first()->is($old));

        $day = now()->subDays(10)->toDateString();
        $this->actingAs($this->admin)->get(route('admin.orders.index', ['from' => $day, 'to' => $day]))
            ->assertViewHas('orders', fn ($p) => $p->total() === 1 && $p->first()->is($old));
        $this->actingAs($this->admin)->get(route('admin.orders.index', ['from' => now()->toDateString()]))
            ->assertViewHas('orders', fn ($p) => $p->total() === 16);
    }

    public function test_bo_loc_khong_hop_le(): void
    {
        $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => 'hacked']))->assertSessionHasErrors('status');
        $this->actingAs($this->admin)->get(route('admin.orders.index', ['from' => '2026-10-10', 'to' => '2026-10-01']))->assertSessionHasErrors('to');
        $this->actingAs($this->admin)->get(route('admin.orders.index', ['from' => 'abc']))->assertSessionHasErrors('from');
    }

    public function test_chi_tiet_don_giu_gia_luc_mua_khi_san_pham_doi_gia(): void
    {
        $order = $this->placeOrder([$this->rose->id => 2]);
        $this->rose->update(['price' => 900000]);

        $this->actingAs($this->admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Người nhận')->assertSee('0900000000')->assertSee('Hồng đỏ')
            ->assertSee('500.000₫')->assertSee('1.000.000₫')
            ->assertSee('Giá hiện tại: 900.000₫');

        $item = $order->items()->first();
        $this->assertSame([500000, 1000000], [$item->price, $item->subtotal]);
        $this->assertSame(1000000, $order->fresh()->total);

        $this->actingAs($this->admin)->get(route('admin.orders.show', 'KHONGTONTAI'))->assertNotFound();
    }

    // ---------- Chuyển trạng thái ----------

    public function test_chuyen_trang_thai_hop_le_den_hoan_thanh(): void
    {
        $order = $this->placeOrder([$this->rose->id => 1]);

        foreach (['confirmed', 'shipping', 'completed'] as $next) {
            $this->transition($order, $next)->assertRedirect(route('admin.orders.show', $order))->assertSessionHas('status');
            $order->refresh();
            $this->assertSame($next, $order->status);
        }

        $this->assertSame('paid', $order->payment_status); // COD hoàn thành = đã thu tiền
        $this->assertSame(49, $this->rose->fresh()->stock);
    }

    public function test_huy_don_hoan_ton_kho(): void
    {
        $order = $this->placeOrder([$this->rose->id => 3, $this->lily->id => 2]);
        $this->assertSame([47, 48], [$this->rose->fresh()->stock, $this->lily->fresh()->stock]);

        $this->transition($order, 'cancelled')->assertSessionHas('status', fn ($m) => str_contains($m, 'hoàn lại'));

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('unpaid', $order->fresh()->payment_status);
        $this->assertSame([50, 50], [$this->rose->fresh()->stock, $this->lily->fresh()->stock]);
        $this->assertSame(2, $order->items()->count()); // dòng hàng vẫn được giữ
    }

    public function test_chuyen_trang_thai_khong_hop_le_bi_tu_choi(): void
    {
        $pending = $this->placeOrder([$this->rose->id => 1]);
        $this->transition($pending, 'completed')->assertSessionHas('error', fn ($m) => str_contains($m, 'Không thể chuyển'));
        $this->transition($pending, 'shipping')->assertSessionHas('error');
        $this->transition($pending, 'hacked')->assertSessionHasErrors('status');
        $this->assertSame('pending', $pending->fresh()->status);

        $completed = $this->placeOrder([$this->rose->id => 1], status: 'completed');
        foreach (['pending', 'cancelled', 'shipping'] as $to) {
            $this->transition($completed, $to)->assertSessionHas('error');
        }
        $this->assertSame('completed', $completed->fresh()->status);

        // Đơn đã hủy không thể "hủy lần nữa" để cộng kho hai lần
        $cancelled = $this->placeOrder([$this->rose->id => 5]);
        $this->transition($cancelled, 'cancelled');
        $stockAfterCancel = $this->rose->fresh()->stock;
        $this->transition($cancelled->fresh(), 'cancelled')->assertSessionHas('error');
        $this->transition($cancelled->fresh(), 'pending')->assertSessionHas('error');
        $this->assertSame($stockAfterCancel, $this->rose->fresh()->stock);
    }

    public function test_tu_choi_cap_nhat_khi_du_lieu_da_cu(): void
    {
        $order = $this->placeOrder([$this->rose->id => 1]);

        // Admin 1 xác nhận đơn; admin 2 vẫn đang nhìn thấy "pending" và bấm hủy
        $this->transition($order, 'confirmed', 'pending');
        $this->transition($order, 'cancelled', 'pending')->assertSessionHas('error', fn ($m) => str_contains($m, 'bởi người khác'));

        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertSame(49, $this->rose->fresh()->stock);
    }

    // ---------- Khách hàng ----------

    public function test_quan_ly_khach_hang(): void
    {
        $this->placeOrder([$this->rose->id => 2], status: 'completed');
        $this->placeOrder([$this->lily->id => 1], status: 'cancelled');
        $other = User::factory()->create(['name' => 'Khách B']);
        $otherOrder = $this->placeOrder([$this->lily->id => 1], $other);

        $this->actingAs($this->admin)->get(route('admin.customers.index'))
            ->assertOk()->assertSee('Khách A')->assertSee('Khách B')
            ->assertViewHas('customers', fn ($p) => $p->total() === 2
                && ! $p->contains('id', $this->admin->id)
                && $p->firstWhere('id', $this->customer->id)->revenue == 1000000);

        $this->actingAs($this->admin)->get(route('admin.customers.index', ['q' => '0911111111']))
            ->assertViewHas('customers', fn ($p) => $p->total() === 1);

        $this->actingAs($this->admin)->get(route('admin.customers.show', $this->customer))
            ->assertOk()->assertSee('a@example.com')->assertDontSee($otherOrder->code)
            ->assertViewHas('stats', fn ($s) => $s === ['orders' => 2, 'completed' => 1, 'cancelled' => 1, 'revenue' => 1000000]);

        // Trang khách hàng không dùng để xem tài khoản admin
        $this->actingAs($this->admin)->get(route('admin.customers.show', $this->admin))->assertNotFound();
    }
}
