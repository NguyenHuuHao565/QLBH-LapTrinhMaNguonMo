<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Gán ảnh mẫu cho sản phẩm: chép database/seeders/images/{slug}.jpg vào
 * storage/app/public/products/ và lưu đường dẫn vào cột image.
 *
 * Chỉ đổi cột image; không đụng giá, tồn kho. Sản phẩm đã có ảnh do admin
 * upload (khác ảnh mẫu) được giữ nguyên. Nguồn ảnh: images/CREDITS.md.
 */
class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $disk = Storage::disk('public');
        $count = 0;

        foreach (Product::all() as $product) {
            $source = database_path("seeders/images/{$product->slug}.jpg");
            $target = "products/{$product->slug}.jpg";

            if (! File::exists($source)) {
                continue;
            }

            if ($product->image && $product->image !== $target) {
                continue; // ảnh do admin upload
            }

            $disk->put($target, File::get($source));
            $product->update(['image' => $target]);
            $count++;
        }

        $this->command?->info("  Đã gán ảnh cho {$count} sản phẩm.");
    }
}
