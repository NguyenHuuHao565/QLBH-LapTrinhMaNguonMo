<?php

// Các thông báo validation dùng trong dự án; quy tắc nào thiếu sẽ dùng bản tiếng Anh (fallback_locale)
return [
    'alpha_dash' => ':Attribute chỉ được chứa chữ, số, dấu gạch ngang và gạch dưới.',
    'boolean' => ':Attribute không hợp lệ.',
    'confirmed' => ':Attribute nhập lại không khớp.',
    'dimensions' => ':Attribute có kích thước không hợp lệ.',
    'email' => ':Attribute phải là địa chỉ email hợp lệ.',
    'exists' => ':Attribute đã chọn không tồn tại.',
    'file' => ':Attribute phải là một tệp tin.',
    'image' => ':Attribute phải là tệp hình ảnh.',
    'in' => ':Attribute đã chọn không hợp lệ.',
    'date' => ':Attribute không phải ngày hợp lệ.',
    'uuid' => ':Attribute không hợp lệ.',
    'integer' => ':Attribute phải là số nguyên.',
    'lowercase' => ':Attribute phải viết thường.',
    'lt' => [
        'numeric' => ':Attribute phải nhỏ hơn :value.',
    ],
    'max' => [
        'string' => ':Attribute không được vượt quá :max ký tự.',
        'numeric' => ':Attribute không được lớn hơn :max.',
        'file' => ':Attribute không được lớn hơn :max KB.',
    ],
    'mimes' => ':Attribute phải có định dạng: :values.',
    'min' => [
        'string' => ':Attribute phải có ít nhất :min ký tự.',
        'numeric' => ':Attribute phải lớn hơn hoặc bằng :min.',
    ],
    'numeric' => ':Attribute phải là số.',
    'regex' => ':Attribute không đúng định dạng.',
    'required' => 'Vui lòng nhập :attribute.',
    'required_with' => 'Vui lòng nhập :attribute.',
    'string' => ':Attribute phải là chuỗi ký tự.',
    'unique' => ':Attribute này đã được sử dụng.',
    'uploaded' => ':Attribute tải lên thất bại.',

    'password' => [
        'letters' => ':Attribute phải chứa ít nhất một chữ cái.',
        'mixed' => ':Attribute phải có cả chữ hoa và chữ thường.',
        'numbers' => ':Attribute phải chứa ít nhất một chữ số.',
        'symbols' => ':Attribute phải chứa ít nhất một ký tự đặc biệt.',
        'uncompromised' => ':Attribute này đã bị lộ trong một vụ rò rỉ dữ liệu, hãy chọn mật khẩu khác.',
    ],

    'attributes' => [
        'name' => 'họ và tên',
        'email' => 'email',
        'phone' => 'số điện thoại',
        'address' => 'địa chỉ',
        'password' => 'mật khẩu',
        'quantity' => 'số lượng',
    ],
];
