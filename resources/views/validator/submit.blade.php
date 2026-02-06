@extends('layouts.validator')

@section('title', 'Ajukan Prestasi Mahasiswa')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 animate-fade-in">
    <!-- Back Button -->
    <div>
        <a href="{{ route('validator.pending.index') }}"
            class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Ajukan Prestasi Mahasiswa</h2>
                <p class="text-gray-500 dark:text-gray-400">Ajukan prestasi atas nama mahasiswa</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('validator.submit.store') }}" method="POST" enctype="multipart/form-data" 
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm"
          x-data="{ loading: false, action: 'pending' }" @submit="loading = true">
        @csrf
        
        <div class="space-y-6">
            <!-- Student Selection with Multi-Select Search -->
            <div>
                <x-student-search-select 
                    name="student_ids" 
                    :required="true" 
                    :error="$errors->first('student_ids')" 
                    :multiple="true"
                />
            </div>

            <!-- Achievement Category -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Jenis Prestasi <span class="text-red-500">*</span>
                </label>
                <select name="achievement_id" required
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg @error('achievement_id') border-red-500 @enderror">
                    <option value="">Pilih Jenis Prestasi</option>
                    @foreach($achievements as $achievement)
                        <option value="{{ $achievement->id }}" {{ old('achievement_id') == $achievement->id ? 'selected' : '' }}>
                            {{ $achievement->name }} ({{ $achievement->category->name ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
                @error('achievement_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Event Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nama Lomba/Event <span class="text-red-500">*</span>
                </label>
                <input type="text" name="event_name" value="{{ old('event_name') }}" required
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg @error('event_name') border-red-500 @enderror"
                    placeholder="Contoh: Olimpiade Matematika Nasional 2024">
                @error('event_name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Level -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Tingkat <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach($levels as $level)
                        @if($level->is_active)
                            <label class="relative">
                                <input type="radio" name="level" value="{{ $level->name }}" {{ old('level') == $level->name ? 'checked' : '' }}
                                    required class="peer sr-only">
                                <div class="px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-center cursor-pointer transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/30 hover:border-gray-300">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 peer-checked:text-emerald-600 dark:peer-checked:text-emerald-400">
                                        {{ $level->name }}
                                    </span>
                                </div>
                            </label>
                        @endif
                    @endforeach
                </div>
                @error('level')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Organizer -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Penyelenggara <span class="text-red-500">*</span>
                </label>
                <input type="text" name="organizer" value="{{ old('organizer') }}" required
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg @error('organizer') border-red-500 @enderror"
                    placeholder="Contoh: Kementerian Pendidikan dan Kebudayaan">
                @error('organizer')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Event Date & Ranking -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tanggal Pelaksanaan <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="event_date" value="{{ old('event_date') }}" required
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg @error('event_date') border-red-500 @enderror">
                    @error('event_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Peringkat/Ranking <span class="text-gray-400 text-xs">(Opsional)</span>
                    </label>
                    <input type="text" name="ranking" value="{{ old('ranking') }}"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                        placeholder="Contoh: Juara 1, Finalis">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Deskripsi <span class="text-gray-400 text-xs">(Opsional)</span>
                </label>
                <textarea name="description" rows="4"
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                    placeholder="Deskripsi tambahan tentang prestasi ini...">{{ old('description') }}</textarea>
            </div>

            <!-- Certificate Upload -->
            <div x-data="{ 
                fileName: '', 
                fileSize: '', 
                fileUrl: '',
                fileType: '',
                showPreview: false
            }">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Sertifikat <span class="text-red-500">*</span>
                </label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
                    <input type="file" name="certificate" accept=".pdf,.jpg,.jpeg,.png" required class="hidden" id="certificate"
                        @change="
                            const file = $event.target.files[0];
                            if (file) {
                                fileName = file.name;
                                fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                                fileUrl = URL.createObjectURL(file);
                                fileType = file.type;
                            }
                        ">
                    <label for="certificate" class="cursor-pointer block">
                        <div x-show="!fileName">
                            <svg class="w-10 h-10 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">Klik untuk upload sertifikat</p>
                            <p class="text-sm text-gray-500">PDF, JPG, PNG (Maks. 5MB)</p>
                        </div>
                        <div x-show="fileName" class="flex items-center justify-center gap-3">
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-left">
                                <p class="text-gray-800 dark:text-white font-medium" x-text="fileName"></p>
                                <p class="text-sm text-gray-500" x-text="fileSize"></p>
                            </div>
                        </div>
                    </label>
                </div>
                
                <!-- Preview Button -->
                <div x-show="fileName" class="mt-3 flex justify-center">
                    <button type="button" @click="showPreview = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Lihat Preview
                    </button>
                </div>

                <!-- Preview Modal -->
                <div x-show="showPreview" x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 p-4"
                    @click.self="showPreview = false">
                    <div class="relative bg-white dark:bg-gray-800 rounded-xl max-w-4xl w-full max-h-[90vh] overflow-auto">
                        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Preview Sertifikat</h3>
                            <button type="button" @click="showPreview = false"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="p-6">
                            <!-- Image Preview -->
                            <div x-show="fileType.startsWith('image/')">
                                <img :src="fileUrl" :alt="fileName" class="max-w-full h-auto mx-auto rounded-lg">
                            </div>
                            <!-- PDF Preview -->
                            <div x-show="fileType === 'application/pdf'" class="w-full" style="height: 70vh;">
                                <iframe :src="fileUrl" class="w-full h-full rounded-lg border border-gray-300 dark:border-gray-600"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
                
                @error('certificate')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Selection -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Tindakan Setelah Submit <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative">
                        <input type="radio" name="submit_action" value="pending" x-model="action" checked class="peer sr-only">
                        <div class="px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-center cursor-pointer transition-all peer-checked:border-yellow-500 peer-checked:bg-yellow-50 dark:peer-checked:bg-yellow-900/30">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 peer-checked:text-yellow-600 dark:peer-checked:text-yellow-400">
                                Pending
                            </span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">Upload dokumen dulu</span>
                        </div>
                    </label>
                    <label class="relative">
                        <input type="radio" name="submit_action" value="approve" x-model="action" class="peer sr-only">
                        <div class="px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-center cursor-pointer transition-all peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/30">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 peer-checked:text-green-600 dark:peer-checked:text-green-400">
                                Approve
                            </span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">Langsung setujui</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Conditional Fields -->
            <div x-show="action === 'approve'" x-cloak class="space-y-4"
                x-data="{ 
                    skipSk: false,
                    waiverReason: '',
                    altDocFileName: '',
                    altDocFileSize: '',
                    altDocFileUrl: '',
                    showAltDocPreview: false
                }">
                
                <!-- Checkbox: Approve tanpa SK -->
                <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="skip_sk" value="1" x-model="skipSk"
                            class="mt-1 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                        <div>
                            <span class="font-medium text-amber-800 dark:text-amber-300">Approve tanpa SK Resmi</span>
                            <p class="text-sm text-amber-700 dark:text-amber-400 mt-1">
                                Centang jika prestasi ini tidak memerlukan SK Resmi atau SK belum tersedia
                            </p>
                        </div>
                    </label>
                </div>

                <!-- SK Resmi Selection (jika tidak skip) -->
                <div x-show="!skipSk" class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg">
                    <label class="block text-sm font-medium text-emerald-700 dark:text-emerald-400 mb-2">
                        Pilih SK Resmi <span class="text-red-500">*</span>
                    </label>
                    <select name="sk_id" :required="action === 'approve' && !skipSk"
                        class="w-full border-emerald-300 dark:border-emerald-600 dark:bg-gray-700 dark:text-white rounded-lg">
                        <option value="">Pilih SK</option>
                        @foreach($skDocuments as $sk)
                            <option value="{{ $sk->id }}" {{ old('sk_id') == $sk->id ? 'selected' : '' }}>
                                {{ $sk->sk_number }} - {{ $sk->title }} ({{ $sk->issued_date->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                    @if($skDocuments->isEmpty())
                        <p class="text-sm text-amber-600 dark:text-amber-400 mt-2 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Belum ada SK tersedia. 
                            <a href="{{ route('validator.sk.index') }}" target="_blank" class="underline hover:text-amber-700">
                                Lihat Manajemen SK
                            </a>
                        </p>
                    @else
                        <p class="text-sm text-emerald-600 dark:text-emerald-400 mt-2">Pilih SK yang sesuai untuk prestasi ini</p>
                    @endif
                </div>

                <!-- Waiver Options (jika skip SK) -->
                <div x-show="skipSk" class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-amber-800 dark:text-amber-300 mb-2">
                            Alasan <span class="text-red-500">*</span>
                        </label>
                        <select name="sk_waiver_reason" x-model="waiverReason" :required="skipSk"
                            class="w-full border-amber-300 dark:border-amber-600 dark:bg-gray-700 dark:text-white rounded-lg">
                            <option value="">Pilih alasan</option>
                            <option value="tingkat_universitas">Prestasi tingkat universitas tidak memerlukan SK</option>
                            <option value="sk_dalam_proses">SK sedang dalam proses</option>
                            <option value="dokumen_alternatif">Menggunakan dokumen alternatif</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <!-- Catatan tambahan -->
                    <div x-show="waiverReason === 'lainnya' || waiverReason">
                        <label class="block text-sm font-medium text-amber-800 dark:text-amber-300 mb-2">
                            Catatan <span x-show="waiverReason === 'lainnya'" class="text-red-500">*</span>
                        </label>
                        <textarea name="sk_waiver_notes" rows="3" :required="waiverReason === 'lainnya'"
                            class="w-full border-amber-300 dark:border-amber-600 dark:bg-gray-700 dark:text-white rounded-lg"
                            placeholder="Jelaskan alasan lebih detail..."></textarea>
                    </div>

                    <!-- Upload dokumen alternatif -->
                    <div x-show="waiverReason === 'dokumen_alternatif'">
                        <label class="block text-sm font-medium text-amber-800 dark:text-amber-300 mb-2">
                            Upload Dokumen Alternatif <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="alternative_document" accept=".pdf,.jpg,.jpeg,.png"
                            :required="waiverReason === 'dokumen_alternatif'"
                            class="w-full border-amber-300 dark:border-amber-600 dark:bg-gray-700 dark:text-white rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-100 file:text-amber-700 hover:file:bg-amber-200"
                            @change="
                                const file = $event.target.files[0];
                                if (file) {
                                    altDocFileName = file.name;
                                    altDocFileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                                    altDocFileUrl = URL.createObjectURL(file);
                                }
                            ">
                        <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">
                            Contoh: Surat Keterangan Fakultas, Surat Tugas, Berita Acara, Email Konfirmasi
                        </p>
                        <div x-show="altDocFileName" class="mt-2 flex items-center gap-2 text-sm text-amber-700 dark:text-amber-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="altDocFileName + ' (' + altDocFileSize + ')'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-medium text-blue-800 dark:text-blue-200 text-sm">Informasi</p>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            <strong>Pending:</strong> Prestasi akan disimpan dengan status "Menunggu" dan Anda akan diarahkan ke halaman upload dokumen tambahan.<br>
                            <strong>Approve:</strong> Prestasi langsung disetujui (wajib upload SK Resmi).
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('validator.pending.index') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Batal
            </a>
            <button type="submit" :disabled="loading"
                class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="loading ? 'Memproses...' : 'Ajukan Prestasi'"></span>
            </button>
        </div>
    </form>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

@stack('scripts')
@endsection
