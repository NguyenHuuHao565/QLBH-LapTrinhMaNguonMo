<x-layout title="Đăng nhập">
    <section class="section auth-section">
        <div class="container">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <i class="bi bi-flower1 auth-icon"></i>
                    <h1 class="auth-title">Đăng nhập</h1>
                    <p class="text-muted mb-0">Chào mừng bạn quay lại Bloomy</p>
                </div>

                <form action="{{ route('login.store') }}" method="post" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               autocomplete="email" required autofocus>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="current-password" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1" @checked(old('remember'))>
                        <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                    </div>

                    <button type="submit" class="btn btn-bloom w-100 btn-lg">Đăng nhập</button>
                </form>

                <p class="text-center mt-4 mb-0">
                    Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a>
                </p>
            </div>
        </div>
    </section>
</x-layout>
