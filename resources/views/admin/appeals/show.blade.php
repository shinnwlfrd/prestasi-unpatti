@extends('layouts.admin')

@section('title', 'Review Banding')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <!-- Back Button -->
        <div>
            <a href="{{ route('admin.appeals.index') }}"
                class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke Daftar Banding
            </a>
        </div>

        <!-- Appeal Status Banner -->
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl lg:rounded-2xl p-6 lg:p-8 xl:p-10">
            <div class="flex items-start gap-4">
                <div
                    class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-amber-900 dark:text-amber-300">Banding Prestasi</h3>
                    <p class="text-sm text-amber-800 dark:text-amber-400 mt-1">
                        Mahasiswa mengajukan banding terhadap keputusan revisi dari validator fakultas
                    </p>
                    <div class="flex items-center gap-4 mt-3">
                        <span class="text-sm text-amber-700 dark:text-amber-400">
                            Diajukan: <strong>{{ $appeal->submitted_at?->format('d M Y H:i') }}</strong>
                        </span>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                            {{ $appeal->status === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' :
        ($appeal->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400') }}">
                            {{ $appeal->status === 'pending' ? 'Pending Review' : ($appeal->status === 'approved' ? 'Diterima' : 'Ditolak') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            <!-- Left Column: Details -->
            <div class="lg:col-span-2 space-y-6 lg:space-y-8">
                <!-- Achievement Header -->
                <div class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl border border-gray-200 dark:border-gray-700 p-6 lg:p-8 xl:p-10 desktop-card-hover">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $achievement->event_name }}</h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $achievement->organizer }}</p>

                    <div class="flex items-center gap-4 mt-4">
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                            {{ $achievement->level === 'Internasional' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' :
        ($achievement->level === 'Nasional' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' :
            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400') }}">
                            {{ $achievement->level }}
                        </span>
                        <span
                            class="px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            {{ $achievement->achievement?->category?->name }}
                        </span>
                        @if($achievement->ranking)
                            <span
                                class="px-3 py-1 rounded-full text-sm font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                Peringkat {{ $achievement->ranking }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Student Information -->
                <div class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl border border-gray-200 dark:border-gray-700 p-6 lg:p-8 xl:p-10 desktop-card-hover">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Mahasiswa</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nama</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                {{ $achievement->student?->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">NIM</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                {{ $achievement->student_id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Program Studi</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                {{ $achievement->student?->program_study_name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Fakultas</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                {{ $achievement->student?->faculty_name ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Faculty Revision Reason -->
                <div class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl border border-gray-200 dark:border-gray-700 p-6 lg:p-8 xl:p-10 desktop-card-hover">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Alasan Revisi dari Fakultas</h2>
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="font-medium text-red-900 dark:text-red-300">Revisi Diminta</p>
                                    <span
                                        class="text-sm text-red-700 dark:text-red-400">{{ $achievement->faculty_validated_at?->format('d M Y H:i') }}</span>
                                </div>
                                <p class="text-sm text-red-800 dark:text-red-300 mb-1">
                                    Validator: <strong>{{ $achievement->facultyValidator?->name }}</strong>
                                </p>
                                <div
                                    class="mt-2 p-3 bg-white dark:bg-gray-800 rounded border border-red-200 dark:border-red-700">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ $achievement->faculty_notes ?? 'Tidak ada catatan' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appeal Reason -->
                <div class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl border border-gray-200 dark:border-gray-700 p-6 lg:p-8 xl:p-10 desktop-card-hover">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Alasan Banding dari Mahasiswa</h2>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-blue-900 dark:text-blue-300 mb-2">Argumentasi Mahasiswa</p>
                                <div
                                    class="p-3 bg-white dark:bg-gray-800 rounded border border-blue-200 dark:border-blue-700">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                        {{ $appeal->reason }}</p>
                                </div>
                                @if($appeal->additional_notes)
                                    <div
                                        class="mt-2 p-3 bg-white dark:bg-gray-800 rounded border border-blue-200 dark:border-blue-700">
                                        <p class="text-xs text-blue-700 dark:text-blue-400 font-medium mb-1">Catatan Tambahan:
                                        </p>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $appeal->additional_notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents -->
                <div class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl border border-gray-200 dark:border-gray-700 p-6 lg:p-8 xl:p-10 desktop-card-hover">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dokumen Pendukung</h2>
                    <div class="space-y-3">
                        @forelse($achievement->documents as $document)
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $document->document_type }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ basename($document->file_path) }}
                                        </p>
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

            <!-- Right Column: Review Form -->
            <div class="space-y-6 lg:space-y-8">
                <!-- Review Form -->
                @if($appeal->status === 'pending')
                    <div class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl border border-gray-200 dark:border-gray-700 p-6 lg:p-8 xl:p-10 desktop-card-hover">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Review Banding</h2>

                        <form action="{{ route('admin.appeals.review', $appeal) }}" method="POST" class="space-y-4">
                            @csrf

                            <!-- Decision -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Keputusan</label>
                                <select name="decision" id="appeal-decision" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <option value="">Pilih Keputusan</option>
                                    <option value="approve">✓ Terima Banding</option>
                                    <option value="reject">✗ Tolak Banding</option>
                                </select>
                            </div>

                            <!-- Decision Info -->
                            <div id="approve-info"
                                class="hidden p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                <p class="text-sm text-green-800 dark:text-green-300">
                                    <strong>Terima Banding:</strong> Prestasi akan dikembalikan ke status "Submitted" untuk
                                    direview ulang oleh validator fakultas.
                                </p>
                            </div>

                            <div id="reject-info"
                                class="hidden p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <p class="text-sm text-red-800 dark:text-red-300">
                                    <strong>Tolak Banding:</strong> Prestasi tetap dalam status "Revisi Fakultas". Mahasiswa
                                    harus memperbaiki sesuai catatan validator.
                                </p>
                            </div>

                            <!-- Admin Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Catatan Admin <span class="text-red-500">*</span>
                                </label>
                                <textarea name="admin_notes" rows="4" required
                                    placeholder="Berikan penjelasan keputusan Anda..."
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Catatan akan dilihat oleh mahasiswa dan validator fakultas
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit"
                                class="w-full px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Kirim Keputusan
                            </button>
                        </form>
                    </div>
                @else
                    <!-- Review Result -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl border border-gray-200 dark:border-gray-700 p-6 lg:p-8 xl:p-10 desktop-card-hover">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hasil Review</h2>
                        <div
                            class="p-4 rounded-lg {{ $appeal->status === 'approved' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' }}">
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 {{ $appeal->status === 'approved' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }}">
                                    <svg class="w-5 h-5 {{ $appeal->status === 'approved' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($appeal->status === 'approved')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        @endif
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="font-medium {{ $appeal->status === 'approved' ? 'text-green-900 dark:text-green-300' : 'text-red-900 dark:text-red-300' }} mb-2">
                                        Banding {{ $appeal->status === 'approved' ? 'Diterima' : 'Ditolak' }}
                                    </p>
                                    <p
                                        class="text-sm {{ $appeal->status === 'approved' ? 'text-green-800 dark:text-green-300' : 'text-red-800 dark:text-red-300' }} mb-1">
                                        Direview oleh: <strong>{{ $appeal->reviewer?->name }}</strong>
                                    </p>
                                    <p
                                        class="text-sm {{ $appeal->status === 'approved' ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                                        {{ $appeal->reviewed_at?->format('d M Y H:i') }}
                                    </p>
                                    @if($appeal->admin_notes)
                                        <div
                                            class="mt-2 p-3 bg-white dark:bg-gray-800 rounded border {{ $appeal->status === 'approved' ? 'border-green-200 dark:border-green-700' : 'border-red-200 dark:border-red-700' }}">
                                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $appeal->admin_notes }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Info -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-blue-800 dark:text-blue-300">
                            <p class="font-medium mb-1">Panduan Review Banding:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Baca alasan revisi dari fakultas</li>
                                <li>Evaluasi argumentasi mahasiswa</li>
                                <li>Periksa dokumen pendukung</li>
                                <li>Berikan keputusan yang adil</li>
                                <li>Tulis catatan yang jelas</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('appeal-decision')?.addEventListener('change', function () {
                const decision = this.value;
                const approveInfo = document.getElementById('approve-info');
                const rejectInfo = document.getElementById('reject-info');

                approveInfo.classList.add('hidden');
                rejectInfo.classList.add('hidden');

                if (decision === 'approve') {
                    approveInfo.classList.remove('hidden');
                } else if (decision === 'reject') {
                    rejectInfo.classList.remove('hidden');
                }
            });
        </script>
    @endpush
@endsection