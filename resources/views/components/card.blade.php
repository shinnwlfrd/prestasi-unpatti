@props([
    'title' => '',
    'padding' => true
])

<div {{ $attributes->merge(['class' => 'rounded-2xl bg-white dark:bg-slate-800 border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden']) }}>
    @if($title)
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">{{ $title }}</h3>
        </div>
    @endif
    <div class="{{ $padding ? 'p-6' : '' }}">
        {{ $slot }}
    </div>
</div>
