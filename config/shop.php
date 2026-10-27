<?php

// Thông tin liên hệ hiển thị ở footer (dữ liệu demo cho đồ án)
return [
    'name' => env('APP_NAME', 'Bloomy Flower Shop'),
    'tagline' => 'Hoa tươi mỗi ngày, gửi trọn yêu thương',
    'address' => env('SHOP_ADDRESS', '140 Lê Trọng Tấn, Phường Tây Thạnh, Quận Tân Phú, TP. Hồ Chí Minh (Trường Đại học Công Thương TP.HCM)'),
    'phone' => env('SHOP_PHONE', '0900 000 000'),
    'email' => env('SHOP_EMAIL', 'lienhe@bloomy.test'),
    'opening_hours' => 'Mở cửa 7:00 – 21:00 mỗi ngày',

    // Tọa độ hiển thị trên bản đồ (Trường ĐH Công Thương TP.HCM, 140 Lê Trọng Tấn — nguồn OpenStreetMap)
    'map' => [
        'lat' => (float) env('SHOP_MAP_LAT', 10.8070354),
        'lng' => (float) env('SHOP_MAP_LNG', 106.6287031),
        'zoom' => 17,
    ],

    // Tài khoản nhận chuyển khoản: mã QR tĩnh (VietQR/Napas 247) xuất từ ví MoMo của chủ cửa hàng.
    // QR tĩnh không chứa số tiền & nội dung — khách tự nhập theo hướng dẫn trên trang đơn hàng.
    'bank' => [
        'name' => env('SHOP_BANK_NAME', 'Ví MoMo (VietQR · Napas 247)'),
        'account_name' => env('SHOP_BANK_ACCOUNT_NAME', 'NGUYEN HUU HAO'),
        'account_masked' => env('SHOP_BANK_ACCOUNT_MASKED', '*******247'),
        'qr_image' => env('SHOP_PAYMENT_QR', 'images/payment-qr.jpg'),
    ],
];
