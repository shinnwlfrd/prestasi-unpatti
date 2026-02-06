@extends('layouts.admin')

@section('title', 'Validasi Universitas - Detail')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.university.index') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <!-- Achievement Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $achievement->event_name }}</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $achievement->organizer }}</p>
                
                <div class="flex items-center gap-4 mt-4">
                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                        {{ $achievement->level === 'Internasional' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' : 
                           ($achievement->level === 'Nasional' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 
                           'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400') }}">
                        {{ $achievement->level }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                        {{ $achievement->achievement?->category?->name }}
                    </span>
                    @if($achievement->ranking)
                    <span class="px-3 py-1 rounded-full text-sm font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                        Peringkat {{ $achievement->ranking }}
                    </span>
                    @endif
                </div>
            </div>
            
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                    Disetujui Fakultas
                </span>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    {{ $achievement->faculty_validated_at?->format('d M Y H:i') }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Student Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Mahasiswa</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nama</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->student?->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">NIM</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->student_id }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Program Studi</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->student?->program_study_name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Fakultas</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->student?->faculty_name ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Achievement Details -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Detail Prestasi</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nama Event</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->event_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Penyelenggara</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->organizer }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Event</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->event_date?->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Peringkat</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->ranking ?? '-' }}</p>
                        </div>
                    </div>
                    @if($achievement->description)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Deskripsi</p>
                        <p class="text-base text-gray-900 dark:text-white mt-1">{{ $achievement->description }}</p>
                    </div>
                    @endif
                    @if($achievement->publication_link)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Link Publikasi</p>
                        <a href="{{ $achievement->publication_link }}" target="_blank" class="text-base text-purple-600 dark:text-purple-400 hover:underline mt-1 inline-block">
                            {{ $achievement->publication_link }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Faculty Validation History -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Validasi Fakultas</h2>
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <p class="font-medium text-green-900 dark:text-green-300">Disetujui oleh Fakultas</p>
                                <span class="text-sm text-green-700 dark:text-green-400">{{ $achievement->faculty_validated_at?->format('d M Y H:i') }}</span>
                            </div>
                            <p class="text-sm text-green-800 dark:text-green-300 mb-1">
                                Validator: <strong>{{ $achievement->facultyValidator?->name }}</strong>
                            </p>
                            @if($achievement->faculty_notes)
                            <div class="mt-2 p-3 bg-white dark:bg-gray-800 rounded border border-green-200 dark:border-green-700">
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $achievement->faculty_notes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dokumen Pendukung</h2>
                <div class="space-y-3">
                    @forelse($achievement->documents as $document)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $document->document_type }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ basename($document->file_path) }}</p>
                            </div>
                        </div>
                        <a href="{{ Storage::url($document->file_path) }}" target="_blank" 
                           class="px-3 py-1.5 text-sm font-medium text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors">
                            Lihat
                        </a>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Tidak ada dokumen</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: University Validation Form -->
        <div class="space-y-6">
            <!-- Validation Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Validasi Universitas</h2>
                
                <form action="{{ route('admin.university.validate', $achievement) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- Action Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Keputusan</label>
                        <select name="action" id="validation-action" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Pilih Keputusan</option>
                            <option value="approve">✓ Setujui & Terbitkan SK</option>
                            <option value="reject">✗ Tolak</option>
                        </select>
                    </div>

                    <!-- SK Selection (shown when approve) -->
                    <div id="sk-selection" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Pilih SK <span class="text-red-500">*</span>
                        </label>
                        <select name="sk_id" id="sk-select"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Pilih SK</option>
                            @foreach($skDocuments ?? [] as $sk)
                                <option value="{{ $sk->id }}">{{ $sk->sk_number }} - {{ $sk->title }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            SK wajib dipilih untuk approval universitas
                        </p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Catatan
                        </label>
                        <textarea name="notes" rows="4"
                                  placeholder="Catatan untuk mahasiswa dan validator fakultas..."
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Kirim Validasi
                    </button>
                </form>
            </div>

            <!-- Validation Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Timeline Validasi</h2>
                <div class="space-y-4">
                    <!-- Submitted -->
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="w-0.5 h-full bg-gray-200 dark:bg-gray-700 mt-1"></div>
                        </div>
                        <div class="flex-1 pb-4">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Diajukan Mahasiswa</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $achievement->submitted_at?->format('d M Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Faculty Approved -->
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="w-0.5 h-full bg-gray-200 dark:bg-gray-700 mt-1"></div>
                        </div>
                        <div class="flex-1 pb-4">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Disetujui Fakultas</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $achievement->faculty_validated_at?->format('d M Y H:i') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">oleh {{ $achievement->facultyValidator?->name }}</p>
                        </div>
                    </div>

                    <!-- University Pending -->
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Menunggu Validasi Universitas</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Sedang dalam proses review</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-purple-800 dark:text-purple-300">
                        <p class="font-medium mb-1">Catatan Penting:</p>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            <li>Prestasi sudah disetujui fakultas</li>
                            <li>SK wajib dipilih saat approval</li>
                            <li>Keputusan ini bersifat final</li>
                            <li>Mahasiswa akan menerima notifikasi</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('validation-action')?.addEventListener('change', function() {
    const action = this.value;
    const skSelection = document.getElementById('sk-selection');
    const skSelect = document.getElementById('sk-select');
    
    if (action === 'approve') {
        skSelection.style.display = 'block';
        skSelect.required = true;
    } else {
        skSelection.style.display = 'none';
        skSelect.required = false;
    }
});
</script>
@endpush
@endsection
