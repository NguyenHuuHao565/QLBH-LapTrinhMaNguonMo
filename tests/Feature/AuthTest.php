<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

        return $admin;
    }

    private function validRegistration(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Nguyễn Văn An',
            'email' => 'an@example.com',
            'phone' => '0901234567',
            'password' => 'matkhau123',
            'password_confirmation' => 'matkhau123',
        ], $overrides);
    }

    // ---------- Đăng ký ----------

    public function test_trang_dang_ky_hien_thi(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('Tạo tài khoản');
    }

    public function test_dang_ky_hop_le_tao_customer_va_hash_mat_khau(): void
    {
        $response = $this->post(route('register.store'), $this->validRegistration());

        $response->assertRedirect(route('account.show'));
        $user = User::where('email', 'an@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(User::ROLE_CUSTOMER, $user->role);
        $this->assertNotSame('matkhau123', $user->password);
        $this->assertTrue(Hash::check('matkhau123', $user->password));
    }

    public function test_khong_the_tu_dang_ky_admin(): void
    {
        $this->post(route('register.store'), $this->validRegistration(['role' => 'ADMIN']));

        $this->assertSame(User::ROLE_CUSTOMER, User::where('email', 'an@example.com')->value('role'));
    }

    public function test_dang_ky_email_trung_bi_tu_choi(): void
    {
        User::factory()->create(['email' => 'an@example.com']);

        $this->from(route('register'))
            ->post(route('register.store'), $this->validRegistration())
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['email' => 'Email này đã được sử dụng.']);

        $this->assertGuest();
        $this->assertSame(1, User::where('email', 'an@example.com')->count());
    }

    public function test_dang_ky_mat_khau_nhap_lai_khong_khop(): void
    {
        $this->post(route('register.store'), $this->validRegistration(['password_confirmation' => 'khac12345']))
            ->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_dang_ky_mat_khau_qua_ngan(): void
    {
        $this->post(route('register.store'), $this->validRegistration(['password' => '123', 'password_confirmation' => '123']))
            ->assertSessionHasErrors('password');
    }

    // ---------- Đăng nhập ----------

    public function test_dang_nhap_dung_vao_trang_tai_khoan(): void
    {
        $user = User::factory()->create(['password' => 'matkhau123']);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'matkhau123'])
            ->assertRedirect(route('account.show'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_dang_nhap_sai_mat_khau(): void
    {
        $user = User::factory()->create(['password' => 'matkhau123']);

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'sai-mat-khau'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Email hoặc mật khẩu không đúng.']);

        $this->assertGuest();
    }

    public function test_dang_nhap_sai_qua_5_lan_bi_khoa_tam(): void
    {
        $user = User::factory()->create(['password' => 'matkhau123']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'sai']);
        }

        // Lần thứ 6, kể cả đúng mật khẩu, vẫn bị chặn
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'matkhau123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_dang_nhap_vao_trang_quan_tri(): void
    {
        $admin = $this->admin();

        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_da_dang_nhap_vao_trang_dang_nhap_bi_chuyen_huong(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('login'))
            ->assertRedirect(route('account.show'));
    }

    // ---------- Đăng xuất ----------

    public function test_dang_xuat(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['du_lieu_cu' => 'x'])
            ->post(route('logout'))
            ->assertRedirect(route('home'))
            ->assertSessionMissing('du_lieu_cu');

        $this->assertGuest();
    }

    public function test_dang_xuat_bang_get_khong_duoc_phep(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dang-xuat')
            ->assertStatus(405);

        $this->assertAuthenticated();
    }

    // ---------- Trang được bảo vệ ----------

    public function test_khach_chua_dang_nhap_bi_chuyen_ve_dang_nhap(): void
    {
        $this->get(route('account.show'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_tai_khoan_hien_thong_tin_nguoi_dung(): void
    {
        $user = User::factory()->create(['name' => 'Trần Thị Hoa', 'phone' => '0909999999']);

        $this->actingAs($user)
            ->get(route('account.show'))
            ->assertOk()
            ->assertSee('Trần Thị Hoa')
            ->assertSee($user->email)
            ->assertSee('0909999999')
            ->assertSee('Khách hàng')
            ->assertDontSee(route('admin.dashboard'));
    }

    // ---------- Phân quyền admin ----------

    public function test_customer_truy_cap_admin_bi_403(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_truy_cap_khu_vuc_quan_tri(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Tổng quan');
    }

    public function test_sau_dang_nhap_quay_lai_trang_dang_muon_vao(): void
    {
        $admin = $this->admin();

        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }
}
