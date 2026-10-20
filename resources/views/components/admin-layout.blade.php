@props(['title' => 'Quản trị'])

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} | Quản trị {{ config('shop.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
</head>
<body class="admin">
    <nav class="navbar navbar-dark admin-topbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}"><i class="bi bi-flower1"></i> Bloomy Admin</a>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('home') }}" class="text-white-50 small d-none d-sm-inline"><i class="bi bi-shop"></i> Xem cửa hàng</a>
                <span class="text-white small d-none d-md-inline"><i class="bi bi-person-badge"></i> {{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i> Đăng xuất</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')])><i class="bi bi-speedometer2"></i> Tổng quan</a>
            <a href="{{ route('admin.orders.index') }}" @class(['active' => request()->routeIs('admin.orders.*')])><i class="bi bi-receipt"></i> Đơn hàng</a>
            <a href="{{ route('admin.customers.index') }}" @class(['active' => request()->routeIs('admin.customers.*')])><i class="bi bi-people"></i> Khách hàng</a>
            <a href="{{ route('admin.products.index') }}" @class(['active' => request()->routeIs('admin.products.*')])><i class="bi bi-flower1"></i> Sản phẩm</a>
            <a href="{{ route('admin.categories.index') }}" @class(['active' => request()->routeIs('admin.categories.*')])><i class="bi bi-tags"></i> Danh mục</a>
        </aside>

        <main class="admin-main">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <h1 class="h3 mb-0">{{ $title }}</h1>
                {{ $actions ?? '' }}
            </div>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="status">
                    <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hộp xác nhận cho các nút nguy hiểm (xóa, ngừng kinh doanh)
        document.querySelectorAll('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (! confirm(form.dataset.confirm)) event.preventDefault();
            });
        });
    </script>
</body>
</html>
