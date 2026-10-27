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

class PaymentAndCancelTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $admin;

    private Product $rose;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['name' => 'Hoa tình yêu', 'slug' => 'hoa-tinh-yeu']);
        $this->rose = Product::create(['category_id' => $category->id, 'name' => 'Hồng đỏ', 'slug' => 'hong-do', 'price' => 500000, 'stock' => 10]);
        $this->customer = User::factory()->create();
        $this->admin = User::factory()->create();
        $this->admin->forceFill(['role' => User::ROLE_ADMIN])->save();
    }

    private function placeOrder(string $method = 'cod', int $qty = 2, ?User $user = null): Order
    {
        return app(OrderPlacer::class)->place($user ?? $this->customer, [$this->rose->id => $qty], [
            'recipient_name' => 'Lan', 'recipient_phone' => '0901234567', 'shipping_address' => 'HCM',
            'payment_method' => $method,
        ], (string) Str::uuid())->fresh();
    }

    private function adminTransition(Order $order, string $to)
    {
        return $this->actingAs($this->admin)->patch(route('admin.orders.update-status', $order), ['status' => $to, 'from' => $order->fresh()->status]);
    }

    // ---------- Chuyển khoản ----------

    public function test_dat_hang_chuyen_khoan_hien_ma_qr_so_tien_va_noi_dung(): void
    {
        $this->actingAs($this->customer)
            ->withSession(['cart' => [$this->rose->id => 2]])
            ->post(route('checkout.store'), [
                'recipient_name' => 'Lan', 'recipient_phone' => '0901234567', 'shipping_address' => 'HCM',
                'payment_method' => 'bank_transfer', 'idempotency_key' => (string) Str::uuid(),
            ])->assertRedirect();

        $order = Order::sole();
        $this->assertSame(['bank_transfer', 'unpaid', 'pending'], [$order->payment_method, $order->payment_status, $order->status]);
        $this->assertNull($order->paid_at);

        foreach ([route('checkout.success', $order), route('orders.show', $order)] as $url) {
            $this->actingAs($this->customer)->get($url)
                ->assertOk()
                ->assertSee('Quét mã để chuyển khoản')
                ->assertSee('images/payment-qr.jpg', false)
                ->assertSee('1.000.000₫')
                ->assertSee($order->code);
        }
    }

    public function test_don_cod_khong_hien_ma_qr(): void
    {
        $order = $this->placeOrder('cod');

        $this->actingAs($this->customer)->get(route('checkout.success', $order))->assertOk()->assertDontSee('Quét mã để chuyển khoản');
    }

    public function test_admin_xac_nhan_da_nhan_chuyen_khoan(): void
    {
        $order = $this->placeOrder('bank_transfer');

        // Chưa nhận tiền thì không được hoàn thành
        $this->adminTransition($order, 'confirmed');
        $this->adminTransition($order, 'shipping');
        $this->adminTransition($order, 'completed')->assertSessionHas('error', fn ($m) => str_contains($m, 'chưa được xác nhận đã nhận tiền'));
        $this->assertSame('shipping', $order->fresh()->status);

        $this->actingAs($this->admin)->patch(route('admin.orders.mark-paid', $order))->assertSessionHas('status');
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->paid_at);

        // Xác nhận lần hai bị từ chối
        $this->actingAs($this->admin)->patch(route('admin.orders.mark-paid', $order))->assertSessionHas('error');

        $this->adminTransition($order, 'completed')->assertSessionHas('status');
        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_khong_xac_nhan_chuyen_khoan_cho_don_cod_hoac_boi_khach(): void
    {
        $cod = $this->placeOrder('cod');
        $this->actingAs($this->admin)->patch(route('admin.orders.mark-paid', $cod))->assertSessionHas('error');
        $this->assertSame('unpaid', $cod->fresh()->payment_status);

        $bank = $this->placeOrder('bank_transfer');
        $this->actingAs($this->customer)->patch(route('admin.orders.mark-paid', $bank))->assertForbidden();
        $this->assertSame('unpaid', $bank->fresh()->payment_status);
    }

    public function test_cod_hoan_thanh_thi_ghi_nhan_thanh_toan(): void
    {
        $order = $this->placeOrder('cod');
        foreach (['confirmed', 'shipping', 'completed'] as $to) {
            $this->adminTransition($order, $to);
        }

        $order->refresh();
        $this->assertSame(['completed', 'paid'], [$order->status, $order->payment_status]);
        $this->assertNotNull($order->paid_at);
    }

    // ---------- Khách tự hủy đơn ----------

    public function test_khach_tu_huy_don_cho_xac_nhan_va_hoan_kho(): void
    {
        $order = $this->placeOrder('cod', 3);
        $this->assertSame(7, $this->rose->fresh()->stock);

        $this->actingAs($this->customer)->get(route('orders.show', $order))->assertSee('Hủy đơn hàng');

        $this->actingAs($this->customer)
            ->patch(route('orders.cancel', $order), ['reason' => 'Đổi ý, không muốn mua nữa'])
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('status');

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('Đổi ý, không muốn mua nữa', $order->cancel_reason);
        $this->assertSame(User::ROLE_CUSTOMER, $order->cancelled_by);
        $this->assertNotNull($order->cancelled_at);
        $this->assertSame(10, $this->rose->fresh()->stock);

        // Hủy lần nữa không cộng kho thêm
        $this->actingAs($this->customer)->patch(route('orders.cancel', $order))->assertSessionHas('error');
        $this->assertSame(10, $this->rose->fresh()->stock);

        $this->actingAs($this->admin)->get(route('admin.orders.show', $order))->assertSee('khách hàng')->assertSee('Đổi ý, không muốn mua nữa');
    }

    public function test_khong_tu_huy_khi_da_xac_nhan(): void
    {
        $order = $this->placeOrder();
        $this->adminTransition($order, 'confirmed');

        $this->actingAs($this->customer)->get(route('orders.show', $order))->assertDontSee('Hủy đơn hàng');
        $this->actingAs($this->customer)->patch(route('orders.cancel', $order))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'liên hệ cửa hàng'));

        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertSame(8, $this->rose->fresh()->stock);
    }

    public function test_khong_tu_huy_don_chuyen_khoan_da_thanh_toan(): void
    {
        $order = $this->placeOrder('bank_transfer');
        $this->actingAs($this->admin)->patch(route('admin.orders.mark-paid', $order));

        $this->actingAs($this->customer)->patch(route('orders.cancel', $order))->assertSessionHas('error');
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_khong_huy_duoc_don_cua_nguoi_khac(): void
    {
        $order = $this->placeOrder();

        $this->actingAs(User::factory()->create())->patch(route('orders.cancel', $order))->assertForbidden();

        auth()->logout();
        $this->patch(route('orders.cancel', $order))->assertRedirect(route('login'));

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_admin_huy_don_luu_ly_do(): void
    {
        $order = $this->placeOrder();

        $this->actingAs($this->admin)->patch(route('admin.orders.update-status', $order), [
            'status' => 'cancelled', 'from' => 'pending', 'cancel_reason' => 'Khách không nghe máy',
        ])->assertSessionHas('status');

        $order->refresh();
        $this->assertSame(['cancelled', 'Khách không nghe máy', User::ROLE_ADMIN], [$order->status, $order->cancel_reason, $order->cancelled_by]);
        $this->actingAs($this->customer)->get(route('orders.show', $order))->assertSee('bởi cửa hàng')->assertSee('Khách không nghe máy');
    }

    // ---------- Trang liên hệ & bản đồ ----------

    public function test_trang_lien_he_co_ban_do(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('id="shop-map"', false)
            ->assertSee('data-lat="10.8070354"', false)
            ->assertSee('leaflet@1.9.4', false)
            ->assertSee('140 Lê Trọng Tấn')
            ->assertSee('google.com/maps/dir', false);

        $this->get(route('home'))->assertSee(route('contact'));
    }
}
