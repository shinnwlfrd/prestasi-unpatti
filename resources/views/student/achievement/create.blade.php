@extends('layouts.app')

@section('title', 'Ajukan Prestasi')
@section('subtitle', 'Form Pengajuan Prestasi Mahasiswa')

@section('content')
    <div class="max-w-4xl mx-auto animate-fade-in">
        <x-card>
            <form action="{{ route('student.achievement.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Achievement Type -->
                <div>
                    <label for="achievement_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Jenis Prestasi <span class="text-red-500">*</span>
                    </label>
                    <select name="achievement_id" id="achievement_id" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <option value="">Pilih Jenis Prestasi</option>
                        @foreach($achievements as $achievement)
                            <option value="{{ $achievement->id }}" {{ old('achievement_id') == $achievement->id ? 'selected' : '' }}>
                                {{ $achievement->category->name }} - {{ $achievement->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('achievement_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Event Name -->
                <div>
                    <label for="event_name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Nama Kegiatan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="event_name" id="event_name" value="{{ old('event_name') }}" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="Contoh: Lomba Karya Tulis Ilmiah Nasional 2024">
                    @error('event_name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level -->
                <div>
                    <label for="level" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Tingkat <span class="text-red-500">*</span>
                    </label>
                    <select name="level" id="level" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <option value="">Pilih Tingkat</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->name }}" {{ old('level') == $level->name ? 'selected' : '' }}>
                                {{ $level->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('level')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Organizer -->
                <div>
                    <label for="organizer" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Penyelenggara <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="organizer" id="organizer" value="{{ old('organizer') }}" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="Contoh: Universitas Indonesia">
                    @error('organizer')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Event Date -->
                <div>
                    <label for="event_date" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Tanggal Kegiatan <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="event_date" id="event_date" value="{{ old('event_date') }}" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    @error('event_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ranking -->
                <div>
                    <label for="ranking" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Peringkat/Pencapaian
                    </label>
                    <input type="text" name="ranking" id="ranking" value="{{ old('ranking') }}"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="Contoh: Juara 1, Finalis, Peserta">
                    @error('ranking')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Deskripsi
                    </label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="Jelaskan prestasi Anda secara singkat...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Certificate Upload -->
                <div>
                    <label for="certificate" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Sertifikat <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="certificate" id="certificate" accept=".pdf,.jpg,.jpeg,.png" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Format: PDF, JPG, JPEG, PNG. Maksimal 2MB</p>
                    @error('certificate')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4">
                    <x-button type="submit" variant="primary" class="flex-1">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Ajukan Prestasi
                    </x-button>
                    <x-button href="{{ route('student.dashboard') }}" variant="secondary">
                        Batal
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection
