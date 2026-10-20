<header class="site-header sticky-top">
    <div class="topbar d-none d-md-block">
        <div class="container d-flex justify-content-between">
            <span><i class="bi bi-truck me-1"></i> Giao hoa nội thành trong ngày</span>
            <a href="tel:{{ preg_replace('/\D/', '', config('shop.phone')) }}">
                <i class="bi bi-telephone me-1"></i> Hotline {{ config('shop.phone') }}
            </a>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand brand" href="{{ route('home') }}">
                <i class="bi bi-flower1"></i> Bloomy
            </a>

            <div class="d-flex align-items-center gap-2 order-lg-last">
                @auth
                    <div class="dropdown">
                        <button class="account-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Tài khoản">
                            <i class="bi bi-person"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">{{ auth()->user()->name }}</h6></li>
                            <li><a class="dropdown-item" href="{{ route('account.show') }}"><i class="bi bi-person-circle me-2"></i>Tài khoản của tôi</a></li>
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}"><i class="bi bi-receipt me-2"></i>Đơn hàng của tôi</a></li>
                            @if (auth()->user()->isAdmin())
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Trang quản trị</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="post">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="account-btn" title="Đăng nhập" aria-label="Đăng nhập">
                        <i class="bi bi-person"></i>
                    </a>
                @endauth

                <a href="{{ route('cart.index') }}" class="cart-btn" title="Giỏ hàng">
                    <i class="bi bi-bag"></i>
                    <span class="cart-count">{{ $cartCount }}</span>
                    <span class="visually-hidden">Giỏ hàng ({{ $cartCount }} sản phẩm)</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                        aria-controls="mainNav" aria-expanded="false" aria-label="Mở menu">
                    <i class="bi bi-list"></i>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-lg-auto">
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('home')) active @endif" href="{{ route('home') }}">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('products.*')) active @endif" href="{{ route('products.index') }}">Sản phẩm</a>
                    </li>
                    <li class="nav-item dropdown">
                        <button class="nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Danh mục</button>
                        <ul class="dropdown-menu">
                            @foreach ($navCategories as $navCategory)
                                <li>
                                    <a class="dropdown-item" href="{{ route('products.index', ['category' => $navCategory->slug]) }}">{{ $navCategory->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#lien-he">Liên hệ</a>
                    </li>
                </ul>

                <form class="header-search my-2 my-lg-0" action="{{ route('products.index') }}" method="get" role="search">
                    {{-- request('q') có thể là mảng nếu URL bị sửa (?q[]=x): chỉ hiển thị khi là chuỗi --}}
                    <input class="form-control" type="search" name="q" value="{{ is_string(request('q')) ? request('q') : '' }}"
                           placeholder="Tìm hoa..." aria-label="Tìm kiếm sản phẩm" maxlength="100">
                    <button type="submit" aria-label="Tìm kiếm"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>
    </nav>
</header>
