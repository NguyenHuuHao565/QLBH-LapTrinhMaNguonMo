@props([
    'seed' => '',
    'label' => '',
])

@php
    // Minh họa bó hoa SVG dùng khi sản phẩm chưa có ảnh; bảng màu chọn theo seed
    $palettes = [
        ['bg' => '#FBEFF1', 'outer' => '#F2A7B8', 'inner' => '#E5809B', 'center' => '#F6D27A'],
        ['bg' => '#FFF5E1', 'outer' => '#F9C74F', 'inner' => '#F4A259', 'center' => '#8C5A2B'],
        ['bg' => '#EEF4EA', 'outer' => '#FFFFFF', 'inner' => '#E4ECDD', 'center' => '#F2C14E'],
        ['bg' => '#F3EEF8', 'outer' => '#CDB0E3', 'inner' => '#A97FCB', 'center' => '#F7E07A'],
        ['bg' => '#FCEBEA', 'outer' => '#E5645C', 'inner' => '#BC3F45', 'center' => '#5A1E1E'],
        ['bg' => '#EAF2F5', 'outer' => '#A9CDE2', 'inner' => '#6FA3C4', 'center' => '#F5F0E1'],
    ];
    $hash = crc32((string) $seed);

    // Ưu tiên màu theo tên hoa (kiểm tra 'đỏ' trước 'hồng' vì có "hồng đỏ")
    $keywords = [
        'đỏ' => 4, 'trắng' => 2, 'baby' => 2, 'cúc' => 2, 'lily' => 2, 'chia buồn' => 2,
        'hướng dương' => 1, 'vàng' => 1, 'khai trương' => 1, 'tulip' => 4,
        'xanh' => 5, 'cẩm tú cầu' => 5, 'tím' => 3, 'lan' => 3, 'hồng' => 0,
    ];
    $text = mb_strtolower((string) $label);
    $index = $hash % count($palettes);
    foreach ($keywords as $word => $paletteIndex) {
        if (str_contains($text, $word)) {
            $index = $paletteIndex;
            break;
        }
    }
    $p = $palettes[$index];
    $tilt = $hash % 30;

    // [x, y, bán kính]
    $flowers = [[200, 140, 62], [126, 190, 50], [274, 190, 50], [162, 242, 42], [240, 242, 42]];
    $dots = [[96, 130], [306, 128], [200, 62], [84, 236], [318, 238], [150, 92], [252, 92]];
@endphp

<svg {{ $attributes->merge(['class' => 'flower-art']) }} viewBox="0 0 400 400" preserveAspectRatio="xMidYMid slice"
     role="img" aria-label="{{ $label }}" xmlns="http://www.w3.org/2000/svg">
    <rect width="400" height="400" fill="{{ $p['bg'] }}"/>
    <circle cx="200" cy="190" r="150" fill="#ffffff" opacity=".45"/>

    <g stroke="#7C9F77" stroke-width="5" stroke-linecap="round" fill="none">
        @foreach ($flowers as [$x, $y])
            <path d="M{{ $x }} {{ $y }} Q{{ ($x + 200) / 2 }} {{ $y + 90 }} 200 360"/>
        @endforeach
    </g>
    <ellipse cx="146" cy="292" rx="34" ry="13" fill="#9DBB92" transform="rotate(-35 146 292)"/>
    <ellipse cx="254" cy="292" rx="34" ry="13" fill="#8DB083" transform="rotate(35 254 292)"/>

    <path d="M112 252 L288 252 L224 378 L176 378 Z" fill="#EAD9C3"/>
    <path d="M112 252 L200 298 L288 252 L262 304 L138 304 Z" fill="#F1E5D4"/>
    <circle cx="200" cy="312" r="11" fill="{{ $p['inner'] }}"/>
    <path d="M200 312 L178 340 M200 312 L222 340" stroke="{{ $p['inner'] }}" stroke-width="5" stroke-linecap="round"/>

    @foreach ($dots as [$x, $y])
        <circle cx="{{ $x }}" cy="{{ $y }}" r="6" fill="#ffffff" stroke="#E7DED2" stroke-width="2"/>
    @endforeach

    @foreach ($flowers as [$x, $y, $r])
        <g transform="translate({{ $x }} {{ $y }}) rotate({{ $tilt }})">
            @for ($k = 0; $k < 6; $k++)
                <ellipse cx="0" cy="{{ -$r * 0.5 }}" rx="{{ $r * 0.42 }}" ry="{{ $r * 0.6 }}"
                         fill="{{ $p['outer'] }}" stroke="{{ $p['inner'] }}" stroke-opacity=".35" transform="rotate({{ $k * 60 }})"/>
            @endfor
            @for ($k = 0; $k < 6; $k++)
                <ellipse cx="0" cy="{{ -$r * 0.3 }}" rx="{{ $r * 0.24 }}" ry="{{ $r * 0.36 }}"
                         fill="{{ $p['inner'] }}" transform="rotate({{ $k * 60 + 30 }})"/>
            @endfor
            <circle r="{{ $r * 0.2 }}" fill="{{ $p['center'] }}"/>
        </g>
    @endforeach
</svg>
