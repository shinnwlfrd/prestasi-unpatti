@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')
@section('subtitle', 'Portal Mahasiswa')

@php
    $userName = $student->name ?? 'Mahasiswa';
    $totalAchievements = $studentAchievements->count();
    $approved = $studentAchievements->where('validation_status', 'Disetujui')->count();
    $pending = $studentAchievements->where('validation_status', 'Menunggu')->count();
    $rejected = $studentAchievements->where('validation_status', 'Ditolak')->count();
    $needRevision = $studentAchievements->where('validation_status', 'Revisi')->count();
@endphp

@section('content')
    <div class="space-y-6 animate-fade-in">
        <!-- Profile Card -->
        <x-card>
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Profile Photo -->
                <div class="flex-shrink-0">
                    <div class="relative">
                        <img src="{{ $student->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($student->name ?? 'M') . '&background=6366f1&color=fff&size=128' }}"
                            alt="Foto Profil"
                            class="w-28 h-28 rounded-2xl object-cover border-4 border-indigo-100 dark:border-indigo-900/50 shadow-lg">
                        <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-full flex items-center justify-center border-4 border-white dark:border-slate-800">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Profile Info -->
                <div class="flex-1">
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $student->name ?? '-' }}</h2>
                            <p class="text-indigo-600 dark:text-indigo-400 font-semibold">{{ $student->student_id ?? '-' }}</p>
                        </div>
                        <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                            Semester {{ $student->semester ?? '-' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-700 rounded-xl p-3 border border-gray-100 dark:border-gray-600">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Fakultas</span>
                            <p class="font-semibold text-gray-800 dark:text-white text-sm mt-0.5 truncate">{{ $student->faculty ?? '-' }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-700 rounded-xl p-3 border border-gray-100 dark:border-gray-600">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Program Studi</span>
                            <p class="font-semibold text-gray-800 dark:text-white text-sm mt-0.5 truncate">{{ $student->program_study ?? '-' }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-700 rounded-xl p-3 border border-gray-100 dark:border-gray-600">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Email</span>
                            <p class="font-semibold text-gray-800 dark:text-white text-sm mt-0.5 truncate">{{ $student->email ?? '-' }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-xl p-3 border border-indigo-100 dark:border-indigo-800">
                            <span class="text-xs text-indigo-500 dark:text-indigo-400">IPK</span>
                            <p class="font-bold text-indigo-600 dark:text-indigo-400 text-lg mt-0.5">{{ number_format($student->gpa ?? 0, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <x-stat-card title="Total Prestasi" :value="$totalAchievements" icon="trophy" color="purple" />
            <x-stat-card title="Menunggu" :value="$pending" icon="clock" color="yellow" />
            <x-stat-card title="Disetujui" :value="$approved" icon="check" color="green" />
            <x-stat-card title="Perlu Revisi" :value="$needRevision" icon="refresh" color="blue" />
            <x-stat-card title="Ditolak" :value="$rejected" icon="x" color="red" />
        </div>

        <!-- Action Button -->
        <div>
            <x-button href="{{ route('student.achievement.create') }}" variant="primary" size="lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajukan Prestasi Baru
            </x-button>
        </div>

        <!-- Achievements Table -->
        <x-card title="Daftar Prestasi Saya" :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Kategori</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Level</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($studentAchievements as $item)
                            @php
                                $statusConfig = [
                                    'Disetujui' => ['label' => 'Disetujui', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', 'icon' => 'check'],
                                    'Ditolak' => ['label' => 'Ditolak', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'icon' => 'x'],
                                    'Menunggu' => ['label' => 'Menunggu', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400', 'icon' => 'clock'],
                                    'Revisi' => ['label' => 'Perlu Revisi', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', 'icon' => 'refresh'],
                                ];
                                $status = $statusConfig[$item->validation_status] ?? $statusConfig['Menunggu'];
                                
                                // Get latest validation log for rejection/revision reason
                                $latestLog = $item->validationLogs()
                                    ->whereIn('new_status', ['Ditolak', 'Revisi'])
                                    ->latest('validated_at')
                                    ->first();
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-800 dark:text-white">{{ $item->event_name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 sm:hidden">{{ $item->achievement->category ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400 hidden sm:table-cell">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                        {{ $item->achievement->category ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400 hidden md:table-cell">{{ $item->level }}</td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ $status['class'] }}">
                                            @if($status['icon'] === 'check')
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            @elseif($status['icon'] === 'x')
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            @elseif($status['icon'] === 'refresh')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            @else
                                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            @endif
                                            {{ $status['label'] }}
                                        </span>
                                        @if($latestLog && $latestLog->notes)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 italic">{{ Str::limit($latestLog->notes, 50) }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('achievements.documents.index', $item) }}" 
                                            class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors"
                                            title="Kelola Dokumen">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </a>
                                        @if($item->validation_status === 'Revisi')
                                            <a href="{{ route('achievements.appeal.create', $item) }}" 
                                                class="p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors"
                                                title="Ajukan Banding">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-gray-800 dark:text-white font-medium">Belum ada prestasi</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Mulai ajukan prestasi pertamamu!</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <!-- Info Card for Need Revision -->
        @if($needRevision > 0)
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold text-blue-800 dark:text-blue-300">Perhatian: Ada {{ $needRevision }} prestasi yang perlu revisi</h4>
                    <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                        Silakan periksa catatan dari validator dan upload dokumen yang diperlukan melalui menu "Kelola Dokumen". 
                        Atau jika Anda merasa sudah memenuhi persyaratan, Anda dapat mengajukan banding melalui tombol banding.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection
