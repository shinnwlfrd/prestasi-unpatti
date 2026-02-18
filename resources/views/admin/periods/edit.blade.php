@extends('layouts.admin')

@section('title', 'Edit Periode Akademik')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Edit Periode Akademik</h2>

            <form action="{{ route('admin.periods.update', $period) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Periode
                            *</label>
                        <input type="text" name="name" value="{{ old('name', $period->name) }}" required
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kode Periode
                            *</label>
                        <input type="text" name="code" value="{{ old('code', $period->code) }}" required
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg @error('code') border-red-500 @enderror">
                        @error('code')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Mulai
                                *</label>
                            <input type="date" name="start_date"
                                value="{{ old('start_date', $period->start_date->format('Y-m-d')) }}" required
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg @error('start_date') border-red-500 @enderror">
                            @error('start_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Selesai
                                *</label>
                            <input type="date" name="end_date"
                                value="{{ old('end_date', $period->end_date->format('Y-m-d')) }}" required
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg @error('end_date') border-red-500 @enderror">
                            @error('end_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Deskripsi</label>
                        <textarea name="description" rows="3"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">{{ old('description', $period->description) }}</textarea>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $period->is_active) ? 'checked' : '' }} id="is_active"
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Aktifkan periode ini
                            (periode lain akan dinonaktifkan)</label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('admin.periods.index') }}"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection