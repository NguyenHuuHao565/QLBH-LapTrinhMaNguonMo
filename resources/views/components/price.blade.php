@props(['product'])

<div {{ $attributes->merge(['class' => 'price']) }}>
    <span class="price-final">{{ number_format($product->finalPrice(), 0, ',', '.') }}₫</span>
    @if ($product->isOnSale())
        <del class="price-old">{{ number_format($product->price, 0, ',', '.') }}₫</del>
    @endif
</div>
