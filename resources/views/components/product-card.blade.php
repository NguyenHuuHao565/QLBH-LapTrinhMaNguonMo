@props(['product'])

<article class="product-card @unless ($product->isInStock()) is-sold-out @endunless">
    <a href="{{ route('products.show', $product) }}" class="product-card-media" tabindex="-1" aria-hidden="true">
        <x-product-image :product="$product" />
        <div class="badges">
            @if ($product->isOnSale())
                <span class="badge-bloom sale">-{{ round(100 - $product->sale_price * 100 / $product->price) }}%</span>
            @endif
            @unless ($product->isInStock())
                <span class="badge-bloom soldout">Hết hàng</span>
            @endunless
        </div>
    </a>

    <div class="product-card-body">
        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="product-category">
            {{ $product->category->name }}
        </a>
        <h3 class="product-name">
            <a href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
        </h3>
        <x-price :product="$product" />
    </div>
</article>
