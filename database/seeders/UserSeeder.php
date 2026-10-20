<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Tạo tài khoản demo.
     *
     * Mật khẩu không viết cứng trong code: lấy từ .env (SEED_ADMIN_PASSWORD,
     * SEED_CUSTOMER_PASSWORD); nếu để trống thì sinh ngẫu nhiên và chỉ in ra
     * màn hình một lần. Mật khẩu luôn được hash nhờ cast 'hashed' của User.
     */
    public function run(): void
    {
        $this->seedUser(
            email: env('SEED_ADMIN_EMAIL', 'admin@bloomy.test'),
            password: env('SEED_ADMIN_PASSWORD'),
            attributes: ['name' => 'Quản trị viên'],
            role: User::ROLE_ADMIN,
        );

        $this->seedUser(
            email: env('SEED_CUSTOMER_EMAIL', 'khach@bloomy.test'),
            password: env('SEED_CUSTOMER_PASSWORD'),
            attributes: [
                'name' => 'Khách hàng mẫu',
                'phone' => '0901234567',
                'address' => '12 Nguyễn Huệ, Quận 1, TP.HCM',
            ],
            role: User::ROLE_CUSTOMER,
        );
    }

    private function seedUser(string $email, ?string $password, array $attributes, string $role): void
    {
        $generated = blank($password);
        $password = $generated ? Str::password(16, symbols: false) : $password;

        $user = User::updateOrCreate(['email' => $email], [...$attributes, 'password' => $password]);

        // role không nằm trong $fillable nên gán trực tiếp
        $user->forceFill(['role' => $role, 'email_verified_at' => now()])->save();

        if ($generated) {
            $this->command?->warn("  {$role}: {$email} / {$password}  (mật khẩu ngẫu nhiên, hãy lưu lại)");
        } else {
            $this->command?->info("  {$role}: {$email} (mật khẩu lấy từ .env)");
        }
    }
}
