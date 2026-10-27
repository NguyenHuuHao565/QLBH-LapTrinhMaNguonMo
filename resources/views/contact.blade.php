@php($map = config('shop.map'))

<x-layout title="Liên hệ" description="Địa chỉ, giờ mở cửa và bản đồ đường đến tiệm hoa Bloomy.">
    @push('head')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
    @endpush

    <section class="page-head">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Liên hệ</li>
                </ol>
            </nav>
            <h1 class="page-title">Ghé thăm Bloomy</h1>
            <p class="page-lead">Tiệm hoa nằm ngay trong khuôn viên Trường Đại học Công Thương TP.HCM.</p>
        </div>
    </section>

    <section class="section pt-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="info-card">
                        <h2 class="info-card-title">Thông tin cửa hàng</h2>
                        <ul class="list-unstyled contact-info">
                            <li><i class="bi bi-geo-alt"></i><span>{{ config('shop.address') }}</span></li>
                            <li><i class="bi bi-telephone"></i><a href="tel:{{ preg_replace('/\D/', '', config('shop.phone')) }}">{{ config('shop.phone') }}</a></li>
                            <li><i class="bi bi-envelope"></i><a href="mailto:{{ config('shop.email') }}">{{ config('shop.email') }}</a></li>
                            <li><i class="bi bi-clock"></i><span>{{ config('shop.opening_hours') }}</span></li>
                        </ul>
                        <div class="d-grid gap-2 mt-4">
                            <a class="btn btn-bloom" target="_blank" rel="noopener"
                               href="https://www.google.com/maps/dir/?api=1&amp;destination={{ $map['lat'] }},{{ $map['lng'] }}">
                                <i class="bi bi-sign-turn-right me-1"></i> Chỉ đường (Google Maps)
                            </a>
                            <a class="btn btn-outline-bloom" target="_blank" rel="noopener"
                               href="https://www.openstreetmap.org/?mlat={{ $map['lat'] }}&amp;mlon={{ $map['lng'] }}#map={{ $map['zoom'] }}/{{ $map['lat'] }}/{{ $map['lng'] }}">
                                <i class="bi bi-map me-1"></i> Xem trên OpenStreetMap
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    {{-- Bản đồ Leaflet + OpenStreetMap (không cần API key) --}}
                    <div id="shop-map" class="shop-map"
                         data-lat="{{ $map['lat'] }}" data-lng="{{ $map['lng'] }}" data-zoom="{{ $map['zoom'] }}"
                         data-title="{{ config('shop.name') }}" data-address="{{ config('shop.address') }}"
                         role="region" aria-label="Bản đồ vị trí cửa hàng">
                        <noscript>
                            <p class="p-4">Bật JavaScript để xem bản đồ, hoặc
                                <a href="https://www.openstreetmap.org/?mlat={{ $map['lat'] }}&amp;mlon={{ $map['lng'] }}">mở trên OpenStreetMap</a>.</p>
                        </noscript>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            (() => {
                const el = document.getElementById('shop-map');
                if (!el || typeof L === 'undefined') return;

                const pos = [Number(el.dataset.lat), Number(el.dataset.lng)];
                const map = L.map(el, { scrollWheelZoom: false }).setView(pos, Number(el.dataset.zoom));

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                }).addTo(map);

                // Nội dung popup tạo bằng textContent để không chèn HTML từ dữ liệu
                const popup = document.createElement('div');
                const title = document.createElement('strong');
                title.textContent = el.dataset.title;
                const address = document.createElement('div');
                address.textContent = el.dataset.address;
                popup.append(title, address);

                L.marker(pos).addTo(map).bindPopup(popup).openPopup();
            })();
        </script>
    @endpush
</x-layout>
