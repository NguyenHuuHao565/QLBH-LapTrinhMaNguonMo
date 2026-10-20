<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chỉ cho tài khoản ADMIN đi qua. Kiểm tra ở phía server cho mọi route
 * trong khu vực quản trị, không phụ thuộc việc ẩn/hiện nút trên giao diện.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Bạn không có quyền truy cập khu vực quản trị.');

        return $next($request);
    }
}
