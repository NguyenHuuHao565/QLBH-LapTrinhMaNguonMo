<x-layout title="Đăng ký">
    <section class="section auth-section">
        <div class="container">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <i class="bi bi-flower1 auth-icon"></i>
                    <h1 class="auth-title">Tạo tài khoản</h1>
                    <p class="text-muted mb-0">Đăng ký để đặt hoa và theo dõi đơn hàng</p>
                </div>

                <form action="{{ route('register.store') }}" method="post" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Họ và tên</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror"
                               autocomplete="name" maxlength="255" required autofocus>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               autocomplete="email" maxlength="255" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Số điện thoại <span class="text-muted">(không bắt buộc)</span></label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                               class="form-control @error('phone') is-invalid @enderror"
                               autocomplete="tel" placeholder="0901234567">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="new-password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Tối thiểu 8 ký tự.</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Nhập lại mật khẩu</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control" autocomplete="new-password" required>
                    </div>

                    <button type="submit" class="btn btn-bloom w-100 btn-lg">Đăng ký</button>
                </form>

                <p class="text-center mt-4 mb-0">
                    Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a>
                </p>
            </div>
        </div>
    </section>
</x-layout>
