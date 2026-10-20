<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('name', '')),
            'is_active' => $this->boolean('is_active'),
            'remove_image' => $this->boolean('remove_image'),
        ]);
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:150'],
            // Slug tự sinh từ tên: chỉ báo lỗi slug khi đã có tên (tránh lỗi trùng lặp khi bỏ trống tên)
            'slug' => ['required_with:name', 'string', 'max:180', 'alpha_dash', Rule::unique('products', 'slug')->ignore($productId)],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'integer', 'min:1000', 'max:999999999'],
            'sale_price' => ['nullable', 'integer', 'min:1000', 'lt:price'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['boolean'],
            // Chỉ nhận ảnh raster thật: kiểm tra cả nội dung (image) lẫn đuôi/MIME (mimes);
            // không nhận SVG (có thể chứa script) hay file thực thi. Tối đa 2 MB.
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=200,min_height=200,max_width=5000,max_height=5000'],
            'remove_image' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'danh mục',
            'name' => 'tên sản phẩm',
            'slug' => 'đường dẫn (slug)',
            'description' => 'mô tả',
            'price' => 'giá bán',
            'sale_price' => 'giá khuyến mãi',
            'stock' => 'số lượng tồn kho',
            'image' => 'ảnh sản phẩm',
        ];
    }

    public function messages(): array
    {
        return [
            'sale_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá bán.',
            'image.dimensions' => 'Ảnh phải có kích thước từ 200×200 đến 5000×5000 px.',
        ];
    }
}
