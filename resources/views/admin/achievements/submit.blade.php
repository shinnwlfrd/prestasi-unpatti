@extends('layouts.admin')

@section('title', 'Ajukan Prestasi Mahasiswa')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    <form action="{{ route('admin.submit.store') }}" method="POST" enctype="multipart/form-data" 
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6"
          x-data="{ loading: false, action: 'pending' }" @submit="loading = true">
        @csrf
        
        <div class="space-y-6">
            <!-- Student Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mahasiswa <span class="text-red-500">*</span>
                </label>
                <select name="student_id" required
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg @error('student_id') border-red-500 @enderror">
                    <option value="">Pilih Mahasiswa</option>
                    @foreach($students as $student)
                        <option value="{{ $student->student_id }}" {{ old('student_id') == $student->student_id ? 'selected' : '' }}>
                            {{ $student->name }} - {{ $student->student_id }}
                        </option>
                    @endforeach
                </select>
                @error('student_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Achievement Category -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Kategori Prestasi <span class="text-red-500">*</span>
                </label>
                <select name="achievement_id" required
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg @error('achievement_id') border-red-500 @enderror">
                    <option value="">Pilih Kategori</option>
                    @foreach($achievements as $achievement)
                        <option value="{{ $achievement->id }}" {{ old('achievement_id') == $achievement->id ? 'selected' : '' }}>
                            {{ $achievement->category }}
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
                                <div class="px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-center cursor-pointer transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/30 hover:border-gray-300">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 peer-checked:text-purple-600 dark:peer-checked:text-purple-400">
                                        {{ $level->name }}
                                    </span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $level->points }} poin</span>
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
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Sertifikat <span class="text-red-500">*</span>
                </label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                    <input type="file" name="certificate" accept=".pdf,.jpg,.jpeg,.png" required class="hidden" id="certificate">
                    <label for="certificate" class="cursor-pointer">
                        <svg class="w-10 h-10 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Klik untuk upload sertifikat</p>
                        <p class="text-sm text-gray-500">PDF, JPG, PNG (Maks. 5MB)</p>
                    </label>
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
                <div class="grid grid-cols-3 gap-3">
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
                    <label class="relative">
                        <input type="radio" name="submit_action" value="reject" x-model="action" class="peer sr-only">
                        <div class="px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-center cursor-pointer transition-all peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/30">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 peer-checked:text-red-600 dark:peer-checked:text-red-400">
                                Reject
                            </span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">Langsung tolak</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Conditional Fields -->
            <div x-show="action === 'approve'" x-cloak class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <label class="block text-sm font-medium text-green-700 dark:text-green-400 mb-2">
                    Upload SK Resmi <span class="text-red-500">*</span>
                </label>
                <input type="file" name="sk_resmi" accept=".pdf,.jpg,.jpeg,.png"
                    :required="action === 'approve'"
                    class="w-full border-green-300 dark:border-green-600 dark:bg-gray-700 dark:text-white rounded-lg">
                <p class="text-sm text-green-600 dark:text-green-400 mt-2">SK Resmi wajib diupload untuk approve prestasi</p>
            </div>

            <div x-show="action === 'reject'" x-cloak class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <label class="block text-sm font-medium text-red-700 dark:text-red-400 mb-2">
                    Alasan Penolakan <span class="text-red-500">*</span>
                </label>
                <textarea name="rejection_reason" rows="3"
                    :required="action === 'reject'"
                    class="w-full border-red-300 dark:border-red-600 dark:bg-gray-700 dark:text-white rounded-lg"
                    placeholder="Jelaskan alasan penolakan..."></textarea>
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
                            <strong>Approve:</strong> Prestasi langsung disetujui (wajib upload SK Resmi).<br>
                            <strong>Reject:</strong> Prestasi langsung ditolak (wajib isi alasan).
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('admin.dashboard') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Batal
            </a>
            <button type="submit" :disabled="loading"
                class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
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
@endsection
