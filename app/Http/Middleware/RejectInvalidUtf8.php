<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Từ chối request có dữ liệu không phải UTF-8 hợp lệ (trình duyệt luôn gửi UTF-8;
 * dữ liệu sai mã hóa chỉ đến từ công cụ/kẻ tấn công). Nếu không chặn ở đây,
 * MySQL sẽ báo lỗi "Incorrect string value" và trả về trang lỗi 500.
 */
class RejectInvalidUtf8
{
    public function handle(Request $request, Closure $next): Response
    {
        $valid = true;
        $input = [$request->query->all(), $request->request->all()];

        array_walk_recursive($input, function ($value) use (&$valid) {
            if (is_string($value) && ! mb_check_encoding($value, 'UTF-8')) {
                $valid = false;
            }
        });

        abort_unless($valid, 400, 'Dữ liệu gửi lên không hợp lệ (sai mã hóa ký tự UTF-8).');

        return $next($request);
    }
}
