<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Từ khóa tìm kiếm ?q=... dạng chuỗi; trả về '' nếu không có hoặc bị gửi dạng mảng (?q[]=x).
     */
    protected function searchTerm(Request $request): string
    {
        $q = $request->query('q');

        return is_string($q) ? trim($q) : '';
    }
}
