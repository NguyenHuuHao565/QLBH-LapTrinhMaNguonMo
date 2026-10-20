<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'regex:/^0\d{9,10}$/'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Chỉ lấy các trường đã validate; role không có trong $fillable
        // nên luôn nhận giá trị mặc định CUSTOMER từ database.
        // Mật khẩu được hash tự động nhờ cast 'hashed' trong model User.
        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.show')->with('status', 'Đăng ký thành công. Chào mừng bạn đến với Bloomy!');
    }
}
