@props(['type' => 'secondary', 'size' => 'sm'])

@php
$colors = [
    'success' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    'danger' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    'secondary' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'primary' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
];

$sizes = [
    'xs' => 'px-2 py-0.5 text-xs',
    'sm' => 'px-2.5 py-1 text-xs',
    'md' => 'px-3 py-1.5 text-sm',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center font-medium rounded-full ' . $colors[$type] . ' ' . $sizes[$size]]) }}>
    {{ $slot }}
</span>
