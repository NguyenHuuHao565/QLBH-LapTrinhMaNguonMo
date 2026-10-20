<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed the categories table.
     */
    public function run(): void
    {
        $categories = [
            'Hoa sinh nhật' => 'Những bó hoa rực rỡ dành tặng ngày sinh nhật.',
            'Hoa tình yêu' => 'Hoa hồng và các mẫu hoa lãng mạn cho người thương.',
            'Hoa khai trương' => 'Kệ hoa chúc mừng khai trương, hồng phát.',
            'Hoa chia buồn' => 'Vòng hoa, kệ hoa trang nghiêm tiễn biệt.',
            'Hoa để bàn' => 'Giỏ và bình hoa trang trí bàn làm việc, phòng khách.',
            'Hoa cưới' => 'Hoa cầm tay cô dâu, hoa cài áo và hoa trang trí tiệc cưới.',
        ];

        // updateOrCreate theo slug: chạy lại seeder không tạo bản ghi trùng
        foreach ($categories as $name => $description) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description, 'is_active' => true],
            );
        }
    }
}
