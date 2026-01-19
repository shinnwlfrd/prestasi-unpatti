@extends('layouts.app')

@section('title', 'Ajukan Prestasi Mahasiswa')
@section('subtitle', 'Panel Validator')

@php
    $userName = auth()->user()->name ?? 'Validator';
@endphp

@section('content')
    <div class="max-w-2xl mx-auto animate-fade-in">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('validator.dashboard') }}"
                class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Dashboard
            </a>
        </div>

        <!-- Form Card -->
        <x-card title="Form Pengajuan Prestasi Mahasiswa">
            <form action="{{ route('validator.submit.store') }}" method="POST" enctype="multipart/form-data"
                class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <!-- Pilih Mahasiswa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Pilih Mahasiswa <span class="text-red-500">*</span>
                    </label>
                    <select name="student_id" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                        <option value="">-- Pilih Mahasiswa --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->student_id }}" {{ old('student_id') == $student->student_id ? 'selected' : '' }}>
                                {{ $student->name }} ({{ $student->student_id }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kategori -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Kategori Prestasi <span class="text-red-500">*</span>
                    </label>
                    <select name="achievement_id" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($achievements as $achievement)
                            <option value="{{ $achievement->id }}" {{ old('achievement_id') == $achievement->id ? 'selected' : '' }}>
                                {{ $achievement->category }}</option>
                        @endforeach
                    </select>
                    @error('achievement_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Event -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Event <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="event_name" value="{{ old('event_name') }}" required
                        placeholder="Contoh: Lomba Debat Nasional 2024"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                    @error('event_name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Level <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach(['Universitas', 'Nasional', 'Internasional'] as $lvl)
                            <label class="relative">
                                <input type="radio" name="level" value="{{ $lvl }}" {{ old('level') == $lvl ? 'checked' : '' }}
                                    required class="peer sr-only">
                                <div
                                    class="px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-center cursor-pointer transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/30 hover:border-gray-300 dark:hover:border-gray-500">
                                    <span
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300 peer-checked:text-emerald-600 dark:peer-checked:text-emerald-400">{{ $lvl }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('level')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Penyelenggara -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Penyelenggara <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="organizer" value="{{ old('organizer') }}" required
                        placeholder="Contoh: Kementerian Pendidikan"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                    @error('organizer')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal & Peringkat -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal Event <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="event_date" value="{{ old('event_date') }}" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                        @error('event_date')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Peringkat/Ranking <span class="text-gray-400 text-xs">(Opsional)</span>
                        </label>
                        <input type="text" name="ranking" value="{{ old('ranking') }}"
                            placeholder="Contoh: Juara 1, Finalis"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                        @error('ranking')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Deskripsi <span class="text-gray-400 text-xs">(Opsional)</span>
                    </label>
                    <textarea name="description" rows="3" placeholder="Jelaskan prestasi yang diraih..."
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">{{ old('description') }}</textarea>
                </div>

                <!-- Upload File -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Upload Sertifikat/Bukti <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="file" name="certificate" required accept="application/pdf,image/jpeg,image/jpg,image/png"
                            class="w-full px-4 py-3 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-900/30 dark:file:text-emerald-400 hover:border-emerald-400 transition-colors">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Format: PDF, JPG, PNG. Ukuran maksimal: 5MB.
                    </p>
                    @error('certificate')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info Box -->
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-emerald-800 dark:text-emerald-300 text-sm">Catatan untuk Validator</h4>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">
                                Pastikan data yang diinput sudah sesuai dengan dokumen bukti yang diunggah. Mahasiswa dapat menambahkan dokumen tambahan setelah prestasi diajukan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" :disabled="loading"
                        class="w-full py-4 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                        <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span x-text="loading ? 'Mengirim...' : 'Ajukan Prestasi'"></span>
                    </button>
                </div>
            </form>
        </x-card>
    </div>
@endsection
