@php
    $isAdmin = auth()->check() && auth()->user()->role === 'Admin';
    $isValidator = auth()->check() && auth()->user()->role === 'Operator';

    // Determine layout based on role
    if ($isAdmin) {
        $layout = 'layouts.admin';
    } elseif ($isValidator) {
        $layout = 'layouts.validator';
    } else {
        $layout = 'layouts.app';
    }
@endphp

@extends($layout)

@section('title', 'Upload Dokumen')

@section('content')
    <div x-data="documentUploader()" class="max-w-4xl mx-auto space-y-6">
        <!-- Header with Back Button -->
        <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-300">
            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/5 -mt-20 -mr-20 rounded-full blur-3xl"></div>
            <div class="p-6 relative">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    @php
                        $isAdmin = auth()->check() && auth()->user()->role === 'Admin';
                        $isValidator = auth()->check() && auth()->user()->role === 'Operator';
                        $isStudent = session('auth_role') === 'student';

                        if ($isAdmin) {
                            $backRoute = route('admin.student-achievements');
                            $themeColor = 'purple';
                        } elseif ($isValidator) {
                            $backRoute = route('validator.pending.index');
                            $themeColor = 'emerald';
                        } else {
                            $backRoute = route('student.dashboard');
                            $themeColor = 'indigo';
                        }
                    @endphp
                    <a href="{{ $backRoute }}"
                        class="p-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 hover:bg-white dark:hover:bg-gray-800 hover:shadow-md rounded-xl transition-all duration-300 group">
                        <svg class="w-5 h-5 text-gray-500 group-hover:text-{{ $themeColor }}-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 bg-{{ $themeColor }}-100 dark:bg-{{ $themeColor }}-900/30 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 text-[10px] font-bold uppercase tracking-wider rounded-md">
                                Dokumentasi Prestasi
                            </span>
                            @if($achievement->validation_status === 'Perlu Revisi')
                                <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-[10px] font-bold uppercase tracking-wider rounded-md">
                                    Butuh Perbaikan
                                </span>
                            @endif
                        </div>
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight truncate">Upload Bukti Tambahan</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium flex items-center gap-1.5 mt-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ $achievement->event_name }}
                        </p>
                    </div>
                </div>

                <!-- Info Alert -->
                <div class="mt-6 flex flex-col md:flex-row gap-4">
                    @if(!isset($isValidatorOrAdmin) || !$isValidatorOrAdmin)
                        <div class="flex-1 p-4 bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/30 rounded-xl flex gap-3 shadow-sm">
                            <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-indigo-900 dark:text-indigo-300 uppercase tracking-wide">Panduan Upload</h4>
                                <p class="text-[11px] text-indigo-700/80 dark:text-indigo-400/80 mt-1 leading-relaxed">
                                    Unggah <strong>Sertifikat</strong> atau bukti pendukung lainnya. Maksimal 2 file pendukung diperbolehkan. SK Resmi akan diunggah oleh petugas saat validasi.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="flex-1 p-4 bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/30 rounded-xl flex gap-3 shadow-sm">
                            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/50 rounded-xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-emerald-900 dark:text-emerald-300 uppercase tracking-wide">Mode Petugas</h4>
                                <p class="text-[11px] text-emerald-700/80 dark:text-emerald-400/80 mt-1 leading-relaxed">
                                    Anda dapat mengunggah semua jenis dokumen termasuk <strong>SK Resmi</strong> untuk prestasi ini.
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($isNonAkademik)
                        <div class="flex-1 p-4 bg-amber-50/50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/30 rounded-xl flex gap-3 shadow-sm">
                            <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/50 rounded-xl flex items-center justify-center text-amber-600 dark:text-amber-400 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-amber-900 dark:text-amber-300 uppercase tracking-wide">Status Non-Akademik</h4>
                                <p class="text-[11px] text-amber-700/80 dark:text-amber-400/80 mt-1 leading-relaxed">
                                    Minimal 2 bukti berbeda diperlukan untuk validasi jenis prestasi non-akademik ini.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        @if(!in_array($achievement->validation_status, ['Disetujui', 'Ditolak']))
            <form id="uploadForm" action="{{ route('achievements.documents.store', $achievement) }}" method="POST"
                enctype="multipart/form-data"
                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8 shadow-sm transition-all duration-300"
                @submit.prevent="submitForm()">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left: File Upload Area -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest flex items-center gap-2">
                                <div class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></div>
                                File Dokumen Pendukung
                            </label>
                            <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold rounded-lg"
                                x-text="(existingSupportingDocsCount + files.length) + ' / ' + maxFiles"></span>
                        </div>

                        <div class="relative group" x-show="existingSupportingDocsCount + files.length < maxFiles">
                            <input type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                                multiple accept=".pdf,.jpg,.jpeg,.png"
                                @change="handleFileSelect($event)">
                            <div class="border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-xl p-8 text-center bg-gray-50/30 dark:bg-gray-900/30 group-hover:bg-white dark:group-hover:bg-gray-900 group-hover:border-indigo-400 dark:group-hover:border-indigo-600 transition-all duration-300">
                                <div class="w-12 h-12 mx-auto bg-indigo-50 dark:bg-indigo-900/50 rounded-xl flex items-center justify-center text-indigo-500 mb-4 transition-transform duration-300 group-hover:scale-110">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <p class="text-[11px] font-bold text-gray-700 dark:text-gray-300">Tarik file ke sini atau klik untuk memilih</p>
                                <p class="text-[10px] text-gray-500 mt-1">PDF, JPG, PNG (Maks. 10MB)</p>
                            </div>
                        </div>

                        <!-- Limit Exceeded Banner -->
                        <div x-show="existingSupportingDocsCount + files.length >= maxFiles" x-cloak
                            class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-xl flex items-center gap-3 animate-fade-in">
                            <div class="p-2 bg-amber-100 dark:bg-amber-900/50 rounded-lg text-amber-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <p class="text-[11px] font-medium text-amber-800 dark:text-amber-400">Kuota dokumen pendukung (2) sudah penuh.</p>
                        </div>

                        <!-- Selected Files Preview -->
                        <div class="space-y-3" x-show="files.length > 0">
                            <template x-for="(file, index) in files" :key="index">
                                <div class="p-3 bg-white dark:bg-gray-900 border border-indigo-100 dark:border-indigo-800 shadow-sm rounded-xl flex items-center gap-3 animate-fade-in group/item">
                                    <div class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-800 flex items-center justify-center overflow-hidden flex-shrink-0 text-indigo-500">
                                        <template x-if="file.preview">
                                            <img :src="file.preview" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!file.preview">
                                            <svg x-show="file.name.endsWith('.pdf')" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                                            </svg>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-bold text-gray-800 dark:text-gray-200 truncate" x-text="file.name"></p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[10px] text-gray-500" x-text="formatFileSize(file.size)"></span>
                                            <select x-model="file.type"
                                                class="text-[10px] font-bold bg-transparent border-none p-0 focus:ring-0 text-indigo-600 dark:text-indigo-400 cursor-pointer">
                                                <option value="">Pilih Jenis...</option>
                                                @foreach($documentTypes as $type => $label)
                                                    <option value="{{ $type }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeFile(index)"
                                        class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all opacity-0 group-hover/item:opacity-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Right: Links & Submit -->
                    <div class="space-y-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 bg-purple-500 rounded-full"></div>
                                    Link Publikasi <span class="text-gray-400 font-medium normal-case tracking-normal">(opsional)</span>
                                </label>
                                <button type="button" @click="addLink()"
                                    class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                    + Tambah Link
                                </button>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(link, index) in externalLinks" :key="index">
                                    <div class="flex gap-2 animate-fade-in group/link">
                                        <input type="text" x-model="link.title" placeholder="Judul"
                                            class="w-1/3 text-[11px] bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
                                        <div class="flex-1 relative">
                                            <input type="url" x-model="link.url" placeholder="https://..."
                                                class="w-full text-[11px] bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 pr-8">
                                            <button type="button" @click="removeLink(index)"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="externalLinks.length === 0" class="text-center py-6 border border-dashed border-gray-100 dark:border-gray-800 rounded-xl">
                                    <p class="text-[10px] text-gray-400 font-medium italic">Tidak ada link tambahan</p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-4 flex flex-col gap-3">
                            <button type="submit"
                                class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/20 transition-all active:scale-95 disabled:opacity-50 disabled:grayscale"
                                :disabled="fileCount === 0 && externalLinks.length === 0 || uploading || !allTypesSelected()">
                                <template x-if="!uploading">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        <span>Mulai Unggah Dokumen</span>
                                    </div>
                                </template>
                                <template x-if="uploading">
                                    <div class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span x-text="'Sedang mengunggah... ' + uploadProgress + '%'"></span>
                                    </div>
                                </template>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @else
            <!-- Read-only mode for approved/rejected achievements -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Mode Lihat Dokumen</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Prestasi ini sudah
                            {{ $achievement->validation_status === 'Disetujui' ? 'disetujui' : 'ditolak' }}. Upload dokumen
                            tidak diperbolehkan.</p>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <a href="{{ $backRoute }}"
                        class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Kembali
                    </a>
                </div>
            </div>
        @endif

        <!-- Existing Documents - Always show if there are documents -->
        @if($achievement->documents->isNotEmpty() || $achievement->certificate)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Dokumen yang Sudah Diunggah</h3>
                    <span
                        class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm font-medium rounded-full">
                        {{ ($achievement->documents->count() + ($achievement->certificate ? 1 : 0)) }} Dokumen
                    </span>
                </div>

                <!-- Certificate (from student_achievements table) -->
                @if($achievement->certificate)
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                                <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                            </svg>
                            Sertifikat Utama
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-4">
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-900/50 group">
                    <div class="aspect-[16/9] relative overflow-hidden bg-gray-200 dark:bg-gray-800 flex items-center justify-center">
                        @php
                            $ext = pathinfo($achievement->certificate, PATHINFO_EXTENSION);
                            $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png']);
                        @endphp
                        
                        @if($isImage)
                            <img src="{{ route('achievements.certificate.preview', $achievement) }}" alt="Sertifikat" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @elseif(strtolower($ext) === 'pdf')
                            <iframe src="{{ route('achievements.certificate.preview', $achievement) }}#toolbar=0" class="w-full h-full border-0" scrolling="no"></iframe>
                            <div class="absolute inset-0 z-10"></div> <!-- Overlay to prevent iframe interaction when not hovered -->
                        @else
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zM8.5 13h1.5v4H8.5v-4zm3 0h1.5v4H11.5v-4zm3 0h1.5v4H14.5v-4z"/>
                                </svg>
                                <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">Berkas {{ strtoupper($ext) }}</p>
                            </div>
                        @endif

                        <!-- View Overlay -->
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-20">
                            <a href="{{ route('achievements.certificate.preview', $achievement) }}" target="_blank" class="p-3 bg-white rounded-full shadow-lg hover:bg-gray-100 transition-colors">
                                <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    <div class="p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">File Sertifikat Utama</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Format: {{ strtoupper($ext) }}</p>
                        </div>
                        
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            @if(!in_array($achievement->validation_status, ['Disetujui', 'Ditolak']))
                            <label class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-semibold bg-purple-600 text-white rounded-lg hover:bg-purple-700 cursor-pointer transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Ganti Berkas Sertifikat
                                <input type="file" class="hidden" onchange="replaceCertificate({{ $achievement->sa_id }}, this)" accept=".pdf,.jpg,.jpeg,.png">
                            </label>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
                @endif

                <!-- Additional Documents (from achievement_documents table) -->
                @if($achievement->documents->isNotEmpty())
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Dokumen Pendukung
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($achievement->documents as $document)
                                <x-document-card :document="$document" :deletable="!in_array($achievement->validation_status, ['Disetujui', 'Ditolak'])" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @else
            <!-- No documents uploaded yet -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-4 text-gray-600 dark:text-gray-400 font-medium">Belum ada dokumen yang diupload</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Upload dokumen pendukung untuk melengkapi pengajuan
                        prestasi</p>
                </div>
            </div>
        @endif
    </div>

    <script>
        function documentUploader() {
            return {
                files: [],
                externalLinks: [],
                isDragging: false,
                uploading: false,
                uploadProgress: 0,
                maxFiles: 2,
                existingSupportingDocsCount: {{ $achievement->documents->whereNotIn('document_type', [App\Models\AchievementDocument::TYPE_SK_RESMI, App\Models\AchievementDocument::TYPE_LINK_PUBLIKASI])->count() }},
                
                get fileCount() {
                    return this.files.length;
                },

                handleFileSelect(event) {
                    const selectedFiles = Array.from(event.target.files);
                    this.addFiles(selectedFiles);
                    event.target.value = '';
                },

                handleDrop(event) {
                    this.isDragging = false;
                    const droppedFiles = Array.from(event.dataTransfer.files);
                    this.addFiles(droppedFiles);
                },

                addFiles(newFiles) {
                    const remainingSlots = this.maxFiles - this.existingSupportingDocsCount - this.files.length;
                    
                    if (newFiles.length > remainingSlots) {
                        showToast('warning', `Batas maksimal 2 dokumen pendukung. Sisa slot: ${remainingSlots}`);
                        newFiles = newFiles.slice(0, remainingSlots);
                    }

                    newFiles.forEach(file => {
                        if (this.validateFile(file)) {
                            const fileObj = {
                                file: file,
                                name: file.name,
                                size: file.size,
                                type: '',
                                preview: null
                            };

                            if (file.type.startsWith('image/')) {
                                const reader = new FileReader();
                                reader.onload = (e) => fileObj.preview = e.target.result;
                                reader.readAsDataURL(file);
                            }

                            this.files.push(fileObj);
                        }
                    });
                },

                validateFile(file) {
                    const maxSize = 10 * 1024 * 1024; // 10MB
                    if (file.size > maxSize) {
                        showToast('warning', `File ${file.name} terlalu besar (Maks 10MB)`);
                        return false;
                    }
                    return true;
                },

                removeFile(index) {
                    this.files.splice(index, 1);
                },

                addLink() {
                    this.externalLinks.push({ title: '', url: '' });
                },

                removeLink(index) {
                    this.externalLinks.splice(index, 1);
                },

                allTypesSelected() {
                    return this.files.every(f => f.type !== '');
                },

                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },

                submitForm() {
                    this.uploading = true;
                    this.uploadProgress = 0;

                    const formData = new FormData();
                    this.files.forEach((fileObj, index) => {
                        formData.append(`documents[${index}]`, fileObj.file);
                        formData.append(`document_types[${index}]`, fileObj.type);
                    });

                    this.externalLinks.forEach((link, index) => {
                        if (link.url) {
                            formData.append(`external_links[${index}][url]`, link.url);
                            formData.append(`external_links[${index}][title]`, link.title || '');
                        }
                    });

                    formData.append('_token', '{{ csrf_token() }}');

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '{{ route("achievements.documents.store", $achievement) }}', true);
                    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                    xhr.setRequestHeader('Accept', 'application/json');

                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable) {
                            this.uploadProgress = Math.round((e.loaded / e.total) * 100);
                        }
                    };

                    xhr.onload = () => {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            window.location.reload();
                        } else {
                            const data = JSON.parse(xhr.responseText);
                            showToast('error', data.message || 'Gagal mengunggah dokumen');
                            this.uploading = false;
                        }
                    };

                    xhr.onerror = () => {
                        showToast('error', 'Terjadi kesalahan jaringan');
                        this.uploading = false;
                    };

                    xhr.send(formData);
                }
            };
        }

        function deleteDocument(id) {
            window.showConfirm('Yakin ingin menghapus dokumen ini?', () => {
                fetch(`/documents/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) window.location.reload();
                    else showToast('error', data.error || 'Gagal menghapus dokumen');
                });
            });
        }

        function replaceDocument(id, input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('file', input.files[0]);
                formData.append('_token', '{{ csrf_token() }}');

                fetch(`/documents/${id}/replace`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) window.location.reload();
                    else {
                        showToast('error', data.error || 'Gagal mengganti dokumen');
                        input.value = '';
                    }
                });
            }
        }

        function replaceCertificate(achievementId, input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('file', input.files[0]);
                formData.append('_token', '{{ csrf_token() }}');

                fetch(`/achievements/${achievementId}/certificate/replace`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) window.location.reload();
                    else {
                        showToast('error', data.error || 'Gagal mengganti sertifikat');
                        input.value = '';
                    }
                });
            }
        }

        function submitDocument(id) {
            window.showConfirm('Kirim dokumen ini untuk verifikasi?', () => {
                fetch(`/documents/${id}/submit`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) window.location.reload();
                    else showToast('error', data.error || 'Gagal mengirim dokumen');
                });
            }, 'success', 'Konfirmasi Kirim');
        }

        function revertDocument(id) {
            const reason = prompt('Alasan pembatalan status:');
            if (reason !== null) {
                fetch(`/documents/${id}/revert`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ reason })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) window.location.reload();
                    else showToast('error', data.error || 'Gagal mengembalikan status');
                });
            }
        }

        function addNoteToDocument(id) {
            const note = prompt('Masukkan catatan:');
            if (note && note.trim()) {
                fetch(`/documents/${id}/add-note`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ note })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) showToast('success', 'Catatan ditambahkan');
                });
            }
        }
    </script>
@endsection