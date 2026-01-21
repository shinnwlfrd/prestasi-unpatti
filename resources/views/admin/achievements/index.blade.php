@extends('layouts.admin')
@section('title', 'Kategori Prestasi')
@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">ID</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Kategori</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Jumlah Prestasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($types as $t)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $t->id }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            @if($t->category && $t->category->icon)
                            <svg class="w-5 h-5" style="color: {{ $t->category->color ?? '#6b7280' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t->category->icon }}"/>
                            </svg>
                            @endif
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium" style="background-color: {{ $t->category->color ?? '#e5e7eb' }}20; color: {{ $t->category->color ?? '#6b7280' }}">
                                {{ $t->category->name ?? 'N/A' }}
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $t->studentAchievements->count() }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
