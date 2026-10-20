<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Product $rose;

    private Product $lily;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['name' => 'Hoa tình yêu', 'slug' => 'hoa-tinh-yeu']);
        $this->rose = Product::create(['category_id' => $category->id, 'name' => 'Hồng đỏ', 'slug' => 'hong-do', 'price' => 500000, 'sale_price' => 450000, 'stock' => 5]);
        $this->lily = Product::create(['category_id' => $category->id, 'name' => 'Lily trắng', 'slug' => 'lily-trang', 'price' => 300000, 'stock' => 10]);
        $this->customer = User::factory()->create(['phone' => '0901234567', 'address' => '12 Lê Lợi']);
    }

    private function shipping(array $overrides = []): array
    {
        return array_merge([
            'recipient_name' => 'Nguyễn Thị Lan',
            'recipient_phone' => '0901 234 567',
            'shipping_address' => '45 Nguyễn Huệ, Quận 1',
            'note' => 'Viết thiệp: Chúc mừng sinh nhật!',
            'payment_method' => 'cod',
            'idempotency_key' => (string) Str::uuid(),
        ], $overrides);
    }

    private function withCart(array $items): static
    {
        return $this->withSession(['cart' => $items]);
    }

    // ---------- Giỏ hàng ----------

    public function test_them_vao_gio_va_tinh_tien_tu_database(): void
    {
        // Giá gửi kèm từ trình duyệt bị bỏ qua
        $this->post(route('cart.add', $this->rose), ['quantity' => 2, 'price' => 1, 'total' => 1])
            ->assertSessionHas('status')
            ->assertSessionHas('cart', [$this->rose->id => 2]);

        $this->post(route('cart.add', $this->rose), ['quantity' => 1]);
        $this->post(route('cart.add', $this->lily), ['quantity' => 1]);

        $response = $this->get(route('cart.index'))->assertOk();
        $this->assertSame(3 * 450000 + 300000, $response->viewData('total'));
        $response->assertSee('1.650.000₫');
    }

    public function test_cap_nhat_va_xoa_san_pham_trong_gio(): void
    {
        $this->withCart([$this->rose->id => 1, $this->lily->id => 2]);

        $this->patch(route('cart.update', $this->rose), ['quantity' => 4])->assertSessionHas('cart.'.$this->rose->id, 4);
        $this->delete(route('cart.remove', $this->lily))->assertSessionMissing('cart.'.$this->lily->id);
    }

    public function test_so_luong_khong_hop_le_bi_tu_choi(): void
    {
        foreach ([0, -3, 'abc', 1.5, ''] as $bad) {
            $this->post(route('cart.add', $this->rose), ['quantity' => $bad])->assertSessionHasErrors('quantity');
        }
        $this->assertSame([], session('cart', []));

        $this->withCart([$this->rose->id => 2]);
        $this->patch(route('cart.update', $this->rose), ['quantity' => 0])->assertSessionHasErrors('quantity');
        $this->patch(route('cart.update', $this->rose), ['quantity' => -1])->assertSessionHasErrors('quantity');
        $this->assertSame(2, session('cart.'.$this->rose->id));
    }

    public function test_vuot_ton_kho_bi_tu_choi(): void
    {
        $this->post(route('cart.add', $this->rose), ['quantity' => 6])
            ->assertSessionHas('error', 'Hồng đỏ: Chỉ còn 5 sản phẩm trong kho.');

        $this->post(route('cart.add', $this->rose), ['quantity' => 4]);
        $this->post(route('cart.add', $this->rose), ['quantity' => 2])->assertSessionHas('error'); // cộng dồn 6 > 5
        $this->assertSame(4, session('cart.'.$this->rose->id));
    }

    public function test_san_pham_het_hang_hoac_ngung_ban_khong_them_duoc(): void
    {
        $this->rose->update(['stock' => 0]);
        $this->post(route('cart.add', $this->rose), ['quantity' => 1])->assertSessionHas('error', 'Hồng đỏ: Sản phẩm đã hết hàng.');

        $this->lily->update(['is_active' => false]);
        $this->post(route('cart.add', $this->lily), ['quantity' => 1])->assertSessionHas('error', 'Lily trắng: Sản phẩm đã ngừng kinh doanh.');

        $this->post(route('cart.add', 99999), ['quantity' => 1])->assertNotFound();
        $this->assertSame([], session('cart', []));
    }

    // ---------- Checkout ----------

    public function test_checkout_yeu_cau_dang_nhap(): void
    {
        $this->withCart([$this->rose->id => 1])->get(route('checkout.create'))->assertRedirect(route('login'));
        $this->post(route('checkout.store'), $this->shipping())->assertRedirect(route('login'));
        $this->assertSame(0, Order::count());
    }

    public function test_gio_hang_trong(): void
    {
        $this->actingAs($this->customer)->get(route('checkout.create'))
            ->assertRedirect(route('cart.index'))->assertSessionHas('error', 'Giỏ hàng của bạn đang trống.');

        $this->actingAs($this->customer)->post(route('checkout.store'), $this->shipping())
            ->assertRedirect(route('cart.index'))->assertSessionHas('error');

        $this->assertSame(0, Order::count());
    }

    public function test_dat_hang_thanh_cong(): void
    {
        $response = $this->actingAs($this->customer)
            ->withCart([$this->rose->id => 2, $this->lily->id => 1])
            ->post(route('checkout.store'), $this->shipping([
                // Dữ liệu giả mạo: đều phải bị bỏ qua
                'total' => 1000, 'status' => 'completed', 'payment_status' => 'paid', 'price' => 1,
            ]));

        $order = Order::with('items')->sole();
        $response->assertRedirect(route('checkout.success', $order));

        $this->assertSame($this->customer->id, $order->user_id);
        $this->assertSame(2 * 450000 + 300000, $order->total);
        $this->assertSame('pending', $order->status);
        $this->assertSame('cod', $order->payment_method);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertSame('0901234567', $order->recipient_phone);
        $this->assertSame('Viết thiệp: Chúc mừng sinh nhật!', $order->note);

        $roseItem = $order->items->firstWhere('product_id', $this->rose->id);
        $this->assertSame(['Hồng đỏ', 450000, 2, 900000], [$roseItem->product_name, $roseItem->price, $roseItem->quantity, $roseItem->subtotal]);

        $this->assertSame(3, $this->rose->fresh()->stock);
        $this->assertSame(9, $this->lily->fresh()->stock);
        $this->assertNull(session('cart'));

        // Trang xác nhận
        $this->actingAs($this->customer)->get(route('checkout.success', $order))
            ->assertOk()->assertSee($order->code)->assertSee('1.200.000₫')->assertSee('Chưa thanh toán');

        // Đổi giá sau khi mua không ảnh hưởng đơn cũ
        $this->rose->update(['price' => 999000, 'sale_price' => null]);
        $this->actingAs($this->customer)->get(route('orders.show', $order))->assertOk()->assertSee('450.000₫')->assertDontSee('999.000₫');
    }

    public function test_ton_kho_giam_sau_khi_them_gio_thi_khong_tao_don(): void
    {
        $this->rose->update(['stock' => 1]); // người khác đã mua bớt

        $this->actingAs($this->customer)
            ->withCart([$this->rose->id => 3])
            ->post(route('checkout.store'), $this->shipping())
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'Hồng đỏ: Chỉ còn 1 sản phẩm trong kho.');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $this->rose->fresh()->stock);
        $this->assertSame(3, session('cart.'.$this->rose->id)); // giỏ vẫn giữ để khách sửa
    }

    public function test_san_pham_het_hang_luc_dat_hang(): void
    {
        $this->rose->update(['stock' => 0]);

        $this->actingAs($this->customer)->withCart([$this->rose->id => 1])->get(route('checkout.create'))
            ->assertRedirect(route('cart.index'));

        $this->actingAs($this->customer)->withCart([$this->rose->id => 1])->post(route('checkout.store'), $this->shipping())
            ->assertSessionHas('error', 'Hồng đỏ: Sản phẩm đã hết hàng.');

        $this->assertSame(0, Order::count());
    }

    public function test_input_khong_hop_le(): void
    {
        $this->actingAs($this->customer)
            ->withCart([$this->rose->id => 1])
            ->post(route('checkout.store'), $this->shipping([
                'recipient_name' => '',
                'recipient_phone' => '12345',
                'shipping_address' => '',
                'payment_method' => 'bank_transfer',
                'delivery_date' => '2000-01-01',
                'note' => str_repeat('a', 501),
                'idempotency_key' => 'khong-phai-uuid',
            ]))
            ->assertSessionHasErrors(['recipient_name', 'recipient_phone', 'shipping_address', 'payment_method', 'delivery_date', 'note', 'idempotency_key']);

        $this->assertSame(0, Order::count());
        $this->assertSame(5, $this->rose->fresh()->stock);
    }

    public function test_gui_lap_cung_khoa_chi_tao_mot_don(): void
    {
        $payload = $this->shipping();

        $first = $this->actingAs($this->customer)->withCart([$this->rose->id => 1])->post(route('checkout.store'), $payload);
        // Gửi lại y hệt (double-click / Back rồi bấm lại), kể cả khi giỏ đã có hàng mới
        $second = $this->actingAs($this->customer)->withCart([$this->rose->id => 1])->post(route('checkout.store'), $payload);

        $order = Order::sole();
        $first->assertRedirect(route('checkout.success', $order));
        $second->assertRedirect(route('checkout.success', $order));
        $this->assertSame(4, $this->rose->fresh()->stock);
    }

    public function test_loi_giua_transaction_rollback_toan_bo(): void
    {
        // Giả lập lỗi database khi đang lưu dòng hàng thứ 2 (sau khi order và dòng 1 đã được ghi)
        $count = 0;
        OrderItem::creating(function () use (&$count) {
            if (++$count === 2) {
                throw new RuntimeException('Mất kết nối database giả lập');
            }
        });

        $this->actingAs($this->customer)
            ->withCart([$this->rose->id => 2, $this->lily->id => 1])
            ->from(route('checkout.create'))
            ->post(route('checkout.store'), $this->shipping())
            ->assertRedirect(route('checkout.create'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Đơn hàng chưa được tạo'));

        $this->assertSame(0, Order::count());
        $this->assertSame(0, OrderItem::count());
        $this->assertSame(5, $this->rose->fresh()->stock);   // không bị trừ dù dòng 1 đã trừ trong transaction
        $this->assertSame(10, $this->lily->fresh()->stock);
        $this->assertSame([$this->rose->id => 2, $this->lily->id => 1], session('cart'));
    }

    // ---------- Xem đơn hàng ----------

    public function test_nguoi_khac_khong_xem_duoc_don(): void
    {
        $this->actingAs($this->customer)->withCart([$this->rose->id => 1])->post(route('checkout.store'), $this->shipping());
        $order = Order::sole();

        $stranger = User::factory()->create();
        $this->actingAs($stranger)->get(route('orders.show', $order))->assertForbidden();
        $this->actingAs($stranger)->get(route('checkout.success', $order))->assertForbidden();
        $this->actingAs($stranger)->get(route('orders.index'))->assertOk()->assertDontSee($order->code);

        $admin = User::factory()->create();
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
        $this->actingAs($admin)->get(route('orders.show', $order))->assertOk()->assertSee($order->code);

        auth()->logout();
        $this->get(route('orders.show', $order))->assertRedirect(route('login'));
    }

    public function test_lich_su_don_hang_cua_khach(): void
    {
        $this->actingAs($this->customer)->withCart([$this->rose->id => 1])->post(route('checkout.store'), $this->shipping());
        $this->actingAs($this->customer)->withCart([$this->lily->id => 2])->post(route('checkout.store'), $this->shipping());

        $response = $this->actingAs($this->customer)->get(route('orders.index'))->assertOk();
        $this->assertSame(2, $response->viewData('orders')->total());
        foreach (Order::all() as $order) {
            $response->assertSee($order->code);
        }
    }
}
