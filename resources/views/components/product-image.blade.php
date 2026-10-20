@props(['product'])

<div {{ $attributes->merge(['class' => 'product-media']) }}>
    @if ($url = $product->imageUrl())
        <img src="{{ $url }}" alt="{{ $product->name }}" loading="lazy">
    @else
        <x-flower-art :seed="$product->slug" :label="$product->name" />
    @endif
</div>
