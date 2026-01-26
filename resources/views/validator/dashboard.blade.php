@extends('layouts.app')

@section('title', 'Dashboard Validator')
@section('subtitle', 'Panel Validator')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@php
    $userName = auth()->user()->name ?? 'Validator';
    $pendingCount = isset($pendingAchievements) ? $pendingAchievements->count() : 0;
@endphp

@section('content')
<div x-data="validatorDashboard()" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Validator</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Selamat datang, {{ auth()->user()->name }}</p>
        </div>
        <a href="{{ route('validator.submit.form') }}" 
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan Prestasi
        </a>
    </div>

    <!-- Pending Achievements Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Menunggu Validasi</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Klik prestasi untuk validasi</p>
                </div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Total: <strong class="text-gray-900 dark:text-white">{{ $pendingAchievements->total() }}</strong> prestasi
                </span>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Prestasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tingkat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal Submit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dokumen</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($pendingAchievements as $achievement)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">{{ substr($achievement->student?->name ?? 'N', 0, 1) }}</span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $achievement->student?->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $achievement->student_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $achievement->event_name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $achievement->organizer }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $achievement->level === 'Internasional' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' : 
                                   ($achievement->level === 'Nasional' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 
                                   'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400') }}">
                                {{ $achievement->level }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $achievement->submitted_at?->format('d M Y') }}
                            <div class="text-xs text-gray-400">{{ $achievement->submitted_at?->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $achievement->documents->count() }} dokumen
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('achievements.documents.index', $achievement) }}" 
                                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                                   title="Upload Dokumen">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </a>
                                <button type="button" 
                                        @click="openModal({{ $achievement->sa_id }})"
                                        class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300 font-medium"
                                        title="Validasi">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada prestasi yang perlu divalidasi</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($pendingAchievements->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $pendingAchievements->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Validasi -->
    <div x-show="showModal" 
         x-cloak
         @click.self="closeModal()"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
        <div @click.away="closeModal()" 
             class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            
            <!-- Loading State -->
            <div x-show="loading" class="p-12 text-center">
                <svg class="animate-spin h-12 w-12 mx-auto text-emerald-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-4 text-gray-600 dark:text-gray-400">Memuat data...</p>
            </div>

            <!-- Content -->
            <div x-show="!loading && achievement" style="display: none;">
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="achievement?.event_name"></h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400" x-text="achievement?.student?.name + ' - ' + achievement?.student_id"></p>
                        </div>
                    </div>
                    <button @click="closeModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6">
                    <!-- Student & Achievement Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Informasi Mahasiswa</h4>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Nama:</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white" x-text="achievement?.student?.name"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">NIM:</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white" x-text="achievement?.student_id"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Fakultas:</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white" x-text="achievement?.student?.faculty || '-'"></dd>
                                </div>
                            </dl>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Informasi Prestasi</h4>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Tingkat:</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white" x-text="achievement?.level"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Penyelenggara:</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white" x-text="achievement?.organizer"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Tanggal:</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white" x-text="achievement?.event_date"></dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Dokumen (<span x-text="achievement?.documents?.length || 0"></span>)</h4>
                        <div class="space-y-2">
                            <template x-for="doc in achievement?.documents" :key="doc.id">
                                <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="doc.type_name"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="doc.file_name"></p>
                                        </div>
                                    </div>
                                    <a :href="'/achievements/documents/' + doc.id + '/preview'" target="_blank"
                                        class="px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40">
                                        Lihat
                                    </a>
                                </div>
                            </template>
                            <div x-show="!achievement?.documents || achievement?.documents?.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
                                Belum ada dokumen
                            </div>
                        </div>
                    </div>

                    <!-- Validation Form -->
                    <form :action="'/validator/achievements/' + achievement?.sa_id + '/validate'" method="POST" id="validationForm">
                        @csrf
                        <div class="space-y-4">
                            <!-- Pesan Instruksi -->
                            <div x-show="!validationAction" class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <p class="text-sm text-blue-700 dark:text-blue-400">
                                    <strong>Instruksi:</strong> Silakan pilih salah satu aksi validasi di bawah ini (Setujui, Tolak, atau Minta Revisi).
                                </p>
                            </div>
                            
                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                                    Catatan Validasi <span class="text-gray-400 text-xs">(Opsional)</span>
                                </label>
                                <textarea name="notes" rows="3" 
                                          placeholder="Tambahkan catatan untuk validasi ini..."
                                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"></textarea>
                            </div>

                            <!-- Dynamic Fields -->
                            <div x-show="validationAction === 'reject'" x-cloak class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <label class="block text-sm font-medium text-red-700 dark:text-red-400 mb-2">
                                    Alasan Penolakan <span class="text-red-500">*</span>
                                </label>
                                <textarea name="rejection_reason" rows="3" 
                                          :required="validationAction === 'reject'"
                                          placeholder="Jelaskan alasan penolakan prestasi ini..."
                                          class="w-full px-4 py-2 border border-red-300 dark:border-red-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
                                <p class="text-xs text-red-600 dark:text-red-400 mt-2">* Wajib diisi</p>
                            </div>

                            <div x-show="validationAction === 'request_revision'" x-cloak class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <label class="block text-sm font-medium text-blue-700 dark:text-blue-400 mb-2">
                                    Alasan Permintaan Revisi <span class="text-blue-500">*</span>
                                </label>
                                <textarea name="revision_reason" rows="3" 
                                          :required="validationAction === 'request_revision'"
                                          placeholder="Jelaskan dokumen apa yang perlu diperbaiki atau dilengkapi..."
                                          class="w-full px-4 py-2 border border-blue-300 dark:border-blue-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                                <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">* Wajib diisi</p>
                            </div>

                            <div x-show="validationAction === 'approve'" x-cloak class="space-y-3">
                                <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg">
                                    <label class="block text-sm font-medium text-emerald-700 dark:text-emerald-400 mb-2">
                                        Pilih Surat Keputusan (SK) <span class="text-red-500">*</span>
                                    </label>
                                    <select name="sk_id" :required="validationAction === 'approve'"
                                            class="w-full px-4 py-2 border border-emerald-300 dark:border-emerald-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                        <option value="">-- Pilih SK --</option>
                                        @foreach($skDocuments ?? [] as $sk)
                                            <option value="{{ $sk->id }}">{{ $sk->sk_number }} - {{ $sk->title }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2">* Wajib dipilih untuk menyetujui prestasi</p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 px-6 py-4 space-y-3">
                    <!-- Tombol Pilih Aksi (tampil jika belum ada aksi dipilih) -->
                    <div x-show="!validationAction" class="grid grid-cols-3 gap-3">
                        <button type="button" @click="validationAction = 'approve'"
                                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Setujui
                        </button>
                        <button type="button" @click="validationAction = 'reject'"
                                class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Tolak
                        </button>
                        <button type="button" @click="validationAction = 'request_revision'"
                                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Minta Revisi
                        </button>
                    </div>
                    
                    <!-- Tombol Submit & Kembali (tampil setelah aksi dipilih) -->
                    <div x-show="validationAction" class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="validationAction = ''"
                                    class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition-colors">
                                ← Kembali
                            </button>
                            <button type="button" @click="submitValidation()"
                                    class="px-4 py-2.5 text-white rounded-lg font-medium transition-colors"
                                    :class="{
                                        'bg-emerald-600 hover:bg-emerald-700': validationAction === 'approve',
                                        'bg-red-600 hover:bg-red-700': validationAction === 'reject',
                                        'bg-blue-600 hover:bg-blue-700': validationAction === 'request_revision'
                                    }">
                                <span x-show="validationAction === 'approve'">✓ Kirim Persetujuan</span>
                                <span x-show="validationAction === 'reject'">✗ Kirim Penolakan</span>
                                <span x-show="validationAction === 'request_revision'">↻ Kirim Permintaan Revisi</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Tombol Tutup -->
                    <button type="button" @click="closeModal()"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validatorDashboard() {
    return {
        showModal: false,
        loading: false,
        achievement: null,
        validationAction: '',
        
        async openModal(achievementId) {
            this.showModal = true;
            this.loading = true;
            this.validationAction = '';
            
            try {
                const response = await fetch(`/api/validator/achievements/${achievementId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Response error:', errorText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                this.achievement = data;
            } catch (error) {
                console.error('Error loading achievement:', error);
                alert('Gagal memuat data prestasi: ' + error.message);
                this.closeModal();
            } finally {
                this.loading = false;
            }
        },
        
        closeModal() {
            this.showModal = false;
            this.achievement = null;
            this.validationAction = '';
        },
        
        submitValidation() {
            const form = document.getElementById('validationForm');
            const action = this.validationAction;
            
            // Validasi berdasarkan aksi
            if (action === 'approve') {
                const skSelect = form.querySelector('[name="sk_id"]');
                if (!skSelect || !skSelect.value) {
                    // Scroll ke field SK
                    skSelect.focus();
                    skSelect.classList.add('border-red-500');
                    setTimeout(() => skSelect.classList.remove('border-red-500'), 2000);
                    return;
                }
            } else if (action === 'reject') {
                const rejectReason = form.querySelector('[name="rejection_reason"]');
                if (!rejectReason || !rejectReason.value.trim()) {
                    // Scroll ke field alasan penolakan
                    rejectReason.focus();
                    rejectReason.classList.add('border-red-500');
                    setTimeout(() => rejectReason.classList.remove('border-red-500'), 2000);
                    return;
                }
            } else if (action === 'request_revision') {
                const revisionReason = form.querySelector('[name="revision_reason"]');
                if (!revisionReason || !revisionReason.value.trim()) {
                    // Scroll ke field alasan revisi
                    revisionReason.focus();
                    revisionReason.classList.add('border-red-500');
                    setTimeout(() => revisionReason.classList.remove('border-red-500'), 2000);
                    return;
                }
            }
            
            // Tambahkan action ke form
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = action;
            form.appendChild(actionInput);
            
            // Submit form
            form.submit();
        }
    }
}
</script>
@endsection
