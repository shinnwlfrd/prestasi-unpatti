@props(['totalCount' => 0, 'context' => 'active'])

@php
$badgeColors = [
    'active' => 'bg-red-500',
    'archive' => 'bg-blue-500',
    'global' => 'bg-purple-500',
];
$badgeColor = $badgeColors[$context] ?? 'bg-red-500';
@endphp

<div class="relative">
    <button 
        onclick="openAnomalyModal()" 
        class="relative p-2 text-gray-600 transition-colors rounded-lg hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
        title="Peringatan Sistem"
    >
        <!-- Bell Icon -->
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        
        <!-- Badge -->
        @if($totalCount > 0)
            <span class="absolute top-0 right-0 flex items-center justify-center w-5 h-5 text-xs font-bold text-white {{ $badgeColor }} rounded-full animate-pulse">
                {{ $totalCount > 99 ? '99+' : $totalCount }}
            </span>
        @endif
    </button>
</div>
