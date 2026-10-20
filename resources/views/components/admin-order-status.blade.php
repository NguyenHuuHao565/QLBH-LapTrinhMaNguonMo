@props(['order'])

<span {{ $attributes->merge(['class' => 'badge text-bg-'.$order->statusColor()]) }}>{{ $order->statusLabel() }}</span>
