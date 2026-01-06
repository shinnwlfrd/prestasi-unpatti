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
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $t->category == 'Akademik' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' }}">
                            {{ $t->category }}
                        </span>
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
