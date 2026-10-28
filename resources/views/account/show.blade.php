<x-layout title="Tài khoản của tôi">
    <section class="page-head">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tài khoản</li>
                </ol>
            </nav>
            <h1 class="page-title">Xin chào, {{ $user->name }}</h1>
        </div>
    </section>

    <section class="section pt-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="info-card h-100">
                        <h2 class="info-card-title">Thông tin tài khoản</h2>
                        <dl class="info-list">
                            <dt>Họ và tên</dt>
                            <dd>{{ $user->name }}</dd>

                            <dt>Email</dt>
                            <dd>{{ $user->email }}</dd>

                            <dt>Số điện thoại</dt>
                            <dd>{{ $user->phone ?: 'Chưa cập nhật' }}</dd>

                            <dt>Địa chỉ</dt>
                            <dd>{{ $user->address ?: 'Chưa cập nhật' }}</dd>

                            <dt>Loại tài khoản</dt>
                            <dd>
                                @if ($user->isAdmin())
                                    <span class="badge-bloom role-admin">Quản trị viên</span>
                                @else
                                    <span class="badge-bloom role-customer">Khách hàng</span>
                                @endif
                            </dd>

                            <dt>Ngày tham gia</dt>
                            <dd>{{ $user->created_at->format('d/m/Y') }}</dd>
                        </dl>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="info-card h-100">
                        <h2 class="info-card-title">Lối tắt</h2>
                        <div class="d-grid gap-2">
                            <a href="{{ route('orders.index') }}" class="btn btn-outline-bloom"><i class="bi bi-receipt me-1"></i> Đơn hàng của tôi</a>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-bloom"><i class="bi bi-flower2 me-1"></i> Tiếp tục mua hoa</a>
                            @if ($user->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-bloom"><i class="bi bi-speedometer2 me-1"></i> Vào trang quản trị</a>
                            @endif
                            <form action="{{ route('logout') }}" method="post" class="d-grid">
                                @csrf
                                <button type="submit" class="btn btn-bloom"><i class="bi bi-box-arrow-right me-1"></i> Đăng xuất</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layout>
