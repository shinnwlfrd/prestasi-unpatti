@props(['percentage' => 0, 'showLabel' => true, 'size' => 'md'])

@php
$color = $percentage >= 80 ? 'bg-green-500' : ($percentage >= 50 ? 'bg-yellow-500' : 'bg-red-500');
$heights = [
    'sm' => 'h-1.5',
    'md' => 'h-2.5',
    'lg' => 'h-4',
];
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($showLabel)
    <div class="flex justify-between mb-1">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $slot }}</span>
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ number_format($percentage, 0) }}%</span>
    </div>
    @endif
    <div class="w-full bg-gray-200 rounded-full {{ $heights[$size] }} dark:bg-gray-700">
        <div class="{{ $color }} {{ $heights[$size] }} rounded-full transition-all duration-300" style="width: {{ min($percentage, 100) }}%"></div>
    </div>
</div>
