@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')
@section('subtitle', 'Portal Mahasiswa')

@php
    $userName = $student->name ?? 'Mahasiswa';
    $totalAchievements = $achievements->count();
    
    // Count by status - support both legacy and new statuses
    $approved = $achievements->whereIn('validation_status', ['Disetujui', 'university_approved'])->count();
    $pending = $achievements->whereIn('validation_status', ['Menunggu', 'submitted', 'faculty_review', 'faculty_approved', 'university_review'])->count();
    $rejected = $achievements->whereIn('validation_status', ['Ditolak', 'faculty_rejected', 'university_rejected'])->count();
    $needRevision = $achievements->whereIn('validation_status', ['Revisi', 'faculty_revision'])->count();
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
                            class="w-28 h-28 rounded-xl object-cover border-4 border-indigo-100 dark:border-indigo-900/50 shadow-lg">
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
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-700 rounded-xl p-3 border border-gray-100 dark:border-gray-600">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Fakultas</span>
                            @if(!empty($student->faculty))
                                <p class="font-semibold text-gray-800 dark:text-white text-sm mt-0.5 truncate">{{ $student->faculty }}</p>
                            @else
                                <p class="text-xs text-amber-500 dark:text-amber-400 mt-0.5 italic">Tidak Ada Data</p>
                            @endif
                        </div>
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-700 rounded-xl p-3 border border-gray-100 dark:border-gray-600">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Jurusan</span>
                            @if(!empty($student->department))
                                <p class="font-semibold text-gray-800 dark:text-white text-sm mt-0.5 truncate">{{ $student->department }}</p>
                            @else
                                <p class="text-xs text-amber-500 dark:text-amber-400 mt-0.5 italic">Tidak Ada Data</p>
                            @endif
                        </div>
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-700 rounded-xl p-3 border border-gray-100 dark:border-gray-600">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Program Studi</span>
                            @if(!empty($student->program_study))
                                <p class="font-semibold text-gray-800 dark:text-white text-sm mt-0.5 truncate">{{ $student->program_study }}</p>
                            @else
                                <p class="text-xs text-amber-500 dark:text-amber-400 mt-0.5 italic">Tidak Ada Data</p>
                            @endif
                        </div>
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-700 rounded-xl p-3 border border-gray-100 dark:border-gray-600">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Email</span>
                            <p class="font-semibold text-gray-800 dark:text-white text-sm mt-0.5 truncate">{{ $student->email ?? '-' }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-xl p-3 border border-indigo-100 dark:border-indigo-800">
                            <span class="text-xs text-indigo-500 dark:text-indigo-400">IPK</span>
                            @if(!is_null($student->gpa) && is_numeric($student->gpa))
                                <p class="font-bold text-indigo-600 dark:text-indigo-400 text-lg mt-0.5">{{ number_format($student->gpa, 2) }}</p>
                            @else
                                <p class="text-xs text-amber-500 dark:text-amber-400 mt-0.5 italic">Tidak Ada Data</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <x-stat-card title="Total Prestasi" :value="$totalAchievements" icon="trophy" color="purple" />
            <x-stat-card title="Menunggu Verifikasi" :value="$pending" icon="clock" color="yellow" />
            <x-stat-card title="Telah Disetujui" :value="$approved" icon="check" color="green" />
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
                        @forelse($achievements as $item)
                            @php
                                $statusConfig = [
                                    // Legacy statuses
                                    'Disetujui' => ['label' => 'Selesai Diverifikasi', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', 'icon' => 'check'],
                                    'Ditolak' => ['label' => 'Ditolak', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'icon' => 'x'],
                                    'Menunggu' => ['label' => 'Menunggu Verifikasi', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400', 'icon' => 'clock'],
                                    'Revisi' => ['label' => 'Perlu Revisi', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', 'icon' => 'refresh'],
                                    // Two-stage validation statuses
                                    'draft' => ['label' => 'Draft', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400', 'icon' => 'clock'],
                                    'submitted' => ['label' => 'Telah Diajukan', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', 'icon' => 'clock'],
                                    'faculty_review' => ['label' => 'Sedang Ditinjau Fakultas', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400', 'icon' => 'clock'],
                                    'faculty_approved' => ['label' => 'Disetujui oleh Fakultas', 'class' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400', 'icon' => 'check'],
                                    'faculty_rejected' => ['label' => 'Ditolak oleh Fakultas', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'icon' => 'x'],
                                    'faculty_revision' => ['label' => 'Perlu Revisi (Fakultas)', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', 'icon' => 'refresh'],
                                    'university_review' => ['label' => 'Sedang Ditinjau Universitas', 'class' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400', 'icon' => 'clock'],
                                    'university_approved' => ['label' => 'Disetujui oleh Universitas', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', 'icon' => 'check'],
                                    'university_rejected' => ['label' => 'Ditolak oleh Universitas', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'icon' => 'x'],
                                    'appeal_submitted' => ['label' => 'Banding Diajukan', 'class' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400', 'icon' => 'clock'],
                                    'appeal_approved' => ['label' => 'Banding Diterima', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', 'icon' => 'check'],
                                    'appeal_rejected' => ['label' => 'Banding Ditolak', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'icon' => 'x'],
                                ];
                                $status = $statusConfig[$item->validation_status] ?? $statusConfig['Menunggu'];
                                
                                // Get latest validation log for rejection/revision reason
                                $latestLog = $item->validationLogs()
                                    ->whereIn('new_status', ['Ditolak', 'Revisi', 'faculty_rejected', 'faculty_revision', 'university_rejected'])
                                    ->latest('validated_at')
                                    ->first();
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-800 dark:text-white">{{ $item->event_name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $item->organizer ?? '-' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 sm:hidden">{{ $item->achievement->category->name ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400 hidden sm:table-cell">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                        {{ $item->achievement->category->name ?? '-' }}
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
                                        @if(in_array($item->validation_status, ['Menunggu', 'submitted', 'faculty_review', 'faculty_approved', 'university_review']))
                                            <!-- Pending/In Review: Show Manage Documents button -->
                                            <button onclick="window.openDocModal({{ $item->sa_id }}, '{{ addslashes($item->event_name) }}', '{{ $item->achievement->category->name ?? '-' }}', '{{ $item->level }}', '{{ $item->certificate }}', {{ $item->documents->toJson() }}, true)" 
                                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors text-sm font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                                Kelola Dokumen
                                            </button>
                                        @elseif(in_array($item->validation_status, ['Disetujui', 'Ditolak', 'university_approved', 'university_rejected', 'faculty_rejected']))
                                            <!-- Approved/Rejected: Show View Documents button (read-only) -->
                                            <button onclick="window.openDocModal({{ $item->sa_id }}, '{{ addslashes($item->event_name) }}', '{{ $item->achievement->category->name ?? '-' }}', '{{ $item->level }}', '{{ $item->certificate }}', {{ $item->documents->toJson() }}, false)" 
                                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                Lihat Dokumen
                                            </button>
                                        @elseif(in_array($item->validation_status, ['Revisi', 'faculty_revision']))
                                            <!-- Revision: Show Request Review button -->
                                            <form action="{{ route('student.achievement.request-review', $item) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" 
                                                    onclick="return confirm('Apakah Anda yakin ingin mengajukan review ulang untuk prestasi ini? Prestasi akan dikembalikan ke antrean verifikasi.')"
                                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors text-sm font-medium">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                    Ajukan Review Ulang
                                                    @if($item->resubmission_count > 0)
                                                        <span class="text-xs">(ke-{{ $item->resubmission_count + 1 }})</span>
                                                    @endif
                                                </button>
                                            </form>
                                        @elseif(in_array($item->validation_status, ['Ditolak', 'faculty_rejected', 'university_rejected']))
                                            <!-- Rejected: Show Delete button -->
                                            <form action="{{ route('student.achievement.destroy', $item) }}" method="POST" class="inline-block" 
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus prestasi ini? Riwayat akan tetap tercatat untuk admin.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors text-sm font-medium">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Hapus Prestasi
                                                </button>
                                            </form>
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
                        Atau jika Anda merasa sudah memenuhi persyaratan, Anda dapat mengajukan review ulang melalui tombol "Ajukan Review Ulang".
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Document Modal -->
    <div id="docModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeDocModal()"></div>
        
        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden" onclick="event.stopPropagation()">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Dokumen Prestasi</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1" id="modalEventName"></p>
                    </div>
                    <button onclick="closeDocModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-180px)]" id="modalContent">
                    <!-- Content will be inserted here -->
                </div>

                <!-- Footer -->
                <div class="flex justify-between items-center p-6 border-t border-gray-200 dark:border-gray-700">
                    <div id="manageButtonContainer"></div>
                    <button onclick="closeDocModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-colors ml-auto">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.openDocModal = function(saId, eventName, category, level, certificate, documents, canManage) {
    // Set event name
    document.getElementById('modalEventName').textContent = eventName;
    
    const getExt = (path) => path.split('.').pop().toLowerCase();
    const isImg = (path) => ['jpg', 'jpeg', 'png'].includes(getExt(path));
    
    // Build content
    let content = `
        <!-- Achievement Info -->
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3 text-sm font-medium text-gray-600 dark:text-gray-400">
                <span class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-md capitalize">${category}</span>
                <span class="text-gray-300 dark:text-gray-600">•</span>
                <span class="px-2 py-0.5 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-md capitalize">${level}</span>
            </div>
        </div>
    `;
    
    // Add certificate if exists
    if (certificate) {
        const ext = getExt(certificate);
        const isImage = isImg(certificate);
        const previewUrl = `/achievements/${saId}/certificate/preview`;
        
        content += `
            <div class="mb-8">
                <h5 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2 uppercase tracking-wider">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Sertifikat Utama
                </h5>
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-900/50 group">
                    <div class="aspect-[16/9] relative overflow-hidden bg-gray-200 dark:bg-gray-800 flex items-center justify-center">
                        ${isImage 
                            ? `<img src="${previewUrl}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">`
                            : ext === 'pdf'
                                ? `<iframe src="${previewUrl}#toolbar=0" class="w-full h-full border-0" scrolling="no"></iframe><div class="absolute inset-0 z-10"></div>`
                                : `<div class="text-center"><svg class="w-16 h-16 mx-auto text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4z"/></svg><p class="mt-2 text-sm font-medium text-gray-500">Berkas ${ext.toUpperCase()}</p></div>`
                        }
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-20">
                            <a href="${previewUrl}" target="_blank" class="p-3 bg-white rounded-full shadow-xl hover:bg-gray-100 transition-all transform hover:scale-110">
                                <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Add documents if exists
    if (documents && documents.length > 0) {
        content += `
            <div>
                <h5 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2 uppercase tracking-wider">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    Dokumen Pendukung (${documents.length})
                </h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        `;
        
        documents.forEach(doc => {
            const ext = getExt(doc.file_path);
            const isImage = isImg(doc.file_path);
            const previewUrl = `/documents/${doc.id}/preview`;
            
            content += `
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-900/50 group">
                    <div class="aspect-[4/3] relative overflow-hidden bg-gray-200 dark:bg-gray-800 flex items-center justify-center">
                        ${isImage 
                            ? `<img src="${previewUrl}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">`
                            : ext === 'pdf'
                                ? `<iframe src="${previewUrl}#toolbar=0" class="w-full h-full border-0" scrolling="no"></iframe><div class="absolute inset-0 z-10"></div>`
                                : `<div class="text-center"><svg class="w-10 h-10 mx-auto text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4z"/></svg></div>`
                        }
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-20">
                            <a href="${previewUrl}" target="_blank" class="p-2 bg-white rounded-full shadow-lg hover:bg-gray-100 transition-all transform hover:scale-110">
                                <svg class="w-5 h-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="p-3 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs font-bold text-gray-900 dark:text-white truncate uppercase tracking-tight">${doc.document_type_label || 'Dokumen'}</p>
                    </div>
                </div>
            `;
        });
        
        content += `
                </div>
            </div>
        `;
    }
    
    if (!certificate && (!documents || documents.length === 0)) {
        content += `
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-gray-600 dark:text-gray-400 font-medium">Belum ada dokumen yang diupload</p>
            </div>
        `;
    }
    
    document.getElementById('modalContent').innerHTML = content;
    
    if (canManage) {
        document.getElementById('manageButtonContainer').innerHTML = `
            <a href="/achievements/${saId}/documents" 
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition-all shadow-lg hover:shadow-indigo-500/25 uppercase text-xs tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Kelola Dokumen
            </a>
        `;
    } else {
        document.getElementById('manageButtonContainer').innerHTML = '';
    }
    
    document.getElementById('docModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
};

window.closeDocModal = function() {
    document.getElementById('docModal').classList.add('hidden');
    document.body.style.overflow = '';
};

// Close on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDocModal();
    }
});
</script>
@endsection
