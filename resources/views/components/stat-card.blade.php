@props([
    'title' => '',
    'value' => 0,
    'icon' => 'chart',
    'color' => 'blue',
    'trend' => null,
    'subtitle' => null
])

@php
    $colorClasses = [
        'blue' => ['bg-blue-50 dark:bg-blue-900/20', 'text-blue-600 dark:text-blue-400', 'from-blue-500 to-indigo-600'],
        'green' => ['bg-emerald-50 dark:bg-emerald-900/20', 'text-emerald-600 dark:text-emerald-400', 'from-emerald-500 to-teal-600'],
        'yellow' => ['bg-amber-50 dark:bg-amber-900/20', 'text-amber-600 dark:text-amber-400', 'from-amber-500 to-orange-600'],
        'red' => ['bg-red-50 dark:bg-red-900/20', 'text-red-600 dark:text-red-400', 'from-red-500 to-pink-600'],
        'purple' => ['bg-purple-50 dark:bg-purple-900/20', 'text-purple-600 dark:text-purple-400', 'from-purple-500 to-pink-600'],
        'orange' => ['bg-orange-50 dark:bg-orange-900/20', 'text-orange-600 dark:text-orange-400', 'from-orange-500 to-red-600'],
    ];
    $c = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 p-5 shadow-sm']) }}>

    
    <div class="relative flex items-start justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ $title }}</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
            @if($subtitle)
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $subtitle }}</p>
            @endif
            @if($trend)
                <div class="flex items-center gap-1 mt-2">
                    <span class="{{ $trend > 0 ? 'text-emerald-500' : 'text-red-500' }} text-sm font-medium">
                        {{ $trend > 0 ? '+' : '' }}{{ $trend }}%
                    </span>
                    <span class="text-xs text-gray-400">dari bulan lalu</span>
                </div>
            @endif
        </div>
        <div class="flex-shrink-0 w-12 h-12 rounded-xl {{ $c[0] }} flex items-center justify-center">
            @if($icon === 'trophy')
                <svg class="w-7 h-7 {{ $c[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            @elseif($icon === 'clock')
                <svg class="w-7 h-7 {{ $c[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @elseif($icon === 'check')
                <svg class="w-7 h-7 {{ $c[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @elseif($icon === 'x')
                <svg class="w-7 h-7 {{ $c[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @elseif($icon === 'users')
                <svg class="w-7 h-7 {{ $c[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13 0V7a4 4 0 10-8 0v4h8z"/></svg>
            @elseif($icon === 'refresh')
                <svg class="w-7 h-7 {{ $c[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            @elseif($icon === 'alert')
                <svg class="w-7 h-7 {{ $c[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            @elseif($icon === 'document')
                <svg class="w-7 h-7 {{ $c[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            @else
                <svg class="w-7 h-7 {{ $c[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            @endif
        </div>
    </div>
</div>
