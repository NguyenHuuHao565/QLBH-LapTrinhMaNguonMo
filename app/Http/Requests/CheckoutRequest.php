<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        // Cho phép khách gõ số điện thoại có dấu cách / dấu chấm
        $this->merge([
            'recipient_phone' => preg_replace('/[\s.\-]/', '', (string) $this->input('recipient_phone')),
        ]);
    }

    /**
     * Chỉ nhận thông tin người nhận. Giá, tổng tiền, trạng thái... nếu có gửi lên đều bị bỏ qua.
     */
    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:100'],
            'recipient_phone' => ['required', 'string', 'regex:/^0\d{9,10}$/'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
            'delivery_date' => ['nullable', 'date', 'after_or_equal:today', 'before_or_equal:+60 days'],
            'payment_method' => ['required', Rule::in(array_keys(Order::PAYMENT_METHODS))],
            'idempotency_key' => ['required', 'uuid'],
        ];
    }

    public function attributes(): array
    {
        return [
            'recipient_name' => 'tên người nhận',
            'recipient_phone' => 'số điện thoại người nhận',
            'shipping_address' => 'địa chỉ giao hàng',
            'note' => 'ghi chú',
            'delivery_date' => 'ngày giao',
            'payment_method' => 'phương thức thanh toán',
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_phone.regex' => 'Số điện thoại phải gồm 10–11 chữ số và bắt đầu bằng 0.',
            'delivery_date.after_or_equal' => 'Ngày giao không được ở quá khứ.',
            'delivery_date.before_or_equal' => 'Chỉ nhận đặt trước trong vòng 60 ngày.',
            'idempotency_key.*' => 'Phiên đặt hàng không hợp lệ, vui lòng tải lại trang thanh toán.',
        ];
    }
}
