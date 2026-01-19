@extends('layouts.app')

@section('title', 'Riwayat Validasi')
@section('subtitle', 'Panel Validator')

@php
    $userName = auth()->user()->name ?? 'Validator';
@endphp

@section('nav-links')
    <div class="hidden md:flex items-center gap-1 bg-gray-100/50 dark:bg-gray-700/50 rounded-xl p-1">
        <a href="{{ route('validator.dashboard') }}"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('validator.dashboard') ? 'bg-white dark:bg-gray-600 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white' }}">
            Menunggu
        </a>
        <a href="{{ route('validator.history') }}"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('validator.history') ? 'bg-white dark:bg-gray-600 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white' }}">
            Riwayat
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6 animate-fade-in">
        <!-- Mobile Tabs -->
        <div class="flex md:hidden gap-2 p-1 bg-gray-100 dark:bg-gray-700 rounded-xl">
            <a href="{{ route('validator.dashboard') }}"
                class="flex-1 py-2 text-center rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('validator.dashboard') ? 'bg-white dark:bg-gray-600 text-emerald-600' : 'text-gray-600' }}">Menunggu</a>
            <a href="{{ route('validator.history') }}"
                class="flex-1 py-2 text-center rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('validator.history') ? 'bg-white dark:bg-gray-600 text-emerald-600' : 'text-gray-600' }}">Riwayat</a>
        </div>

        <!-- History Table -->
        <x-card title="Riwayat Validasi" :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Mahasiswa</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">
                                Prestasi</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">
                                Tanggal</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">
                                SK</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden xl:table-cell">
                                Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                                            {{ strtoupper(substr($log->studentAchievement->student->name ?? 'M', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-800 dark:text-white">
                                                {{ $log->studentAchievement->student->name ?? '-' }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $log->studentAchievement->student->student_id ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 hidden sm:table-cell">
                                    <div class="text-gray-800 dark:text-white font-medium">
                                        {{ Str::limit($log->studentAchievement->event_name ?? '-', 30) }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $log->studentAchievement->achievement->category ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-sm hidden md:table-cell">
                                    {{ $log->validated_at ? \Carbon\Carbon::parse($log->validated_at)->format('d M Y H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusStyles = [
                                            'Disetujui' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                            'Ditolak' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        ];
                                    @endphp
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ $statusStyles[$log->new_status] ?? 'bg-gray-100 text-gray-700' }}">
                                        @if($log->new_status === 'Disetujui')
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                        {{ $log->new_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 hidden lg:table-cell">
                                    @if($log->sk_document)
                                        <a href="{{ asset('storage/' . $log->sk_document) }}" target="_blank"
                                            class="inline-flex items-center gap-1.5 text-blue-600 dark:text-blue-400 hover:text-blue-700 text-sm font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            View
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-sm">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-sm hidden xl:table-cell">
                                    <span class="truncate max-w-[200px] block"
                                        title="{{ $log->notes }}">{{ $log->notes ?? '-' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div
                                            class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-gray-800 dark:text-white font-medium">Belum ada riwayat</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Validasi prestasi akan muncul di
                                                sini.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $logs->links() }}
                </div>
            @endif
        </x-card>
    </div>
@endsection