@extends('layouts.admin')

@section('title', 'Manajemen SK')

@section('content')
    <div class="max-w-7xl mx-auto" x-data="skManagement()">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Manajemen Surat Keputusan (SK)</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Kelola SK untuk approval prestasi mahasiswa</p>
            </div>
            <button @click="showModal = true"
                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Upload SK Baru
            </button>
        </div>

        <!-- Main Search Bar -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6 p-4">
            <form action="{{ route('admin.sk.index') }}" method="GET" class="relative">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="block w-full pl-10 pr-10 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg leading-5 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                        placeholder="Cari berdasarkan No SK, Judul, NIM, Nama Mahasiswa, atau Nama Lomba...">

                    @if(request('search'))
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <a href="{{ route('admin.sk.index') }}"
                                class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-full hover:bg-gray-100 dark:hover:bg-gray-600"
                                title="Hapus pencarian">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Nomor SK</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Judul</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Metode Upload</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tanggal Terbit</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Penerbit</th>
                            <th
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($skDocuments as $sk)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $sk->sk_number }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-900 dark:text-white">{{ $sk->title }}</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $sk->assignments_count }} prestasi ter-assign
                                    </p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($sk->file_type === 'file')
                                        <span
                                            class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full">File
                                            PDF</span>
                                    @elseif($sk->file_type === 'link')
                                        <span
                                            class="px-2 py-1 text-xs bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400 rounded-full">Link
                                            Eksternal</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                    {{ $sk->issued_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                    {{ $sk->issued_by }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Lihat Dokumen -->
                                        @if($sk->file_type === 'file')
                                            <a href="{{ route('admin.sk.preview', $sk) }}" target="_blank"
                                                class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                                title="Lihat Dokumen">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        @elseif($sk->file_type === 'link')
                                            <a href="{{ $sk->external_link }}" target="_blank"
                                                class="p-2 text-cyan-600 hover:bg-cyan-50 dark:hover:bg-cyan-900/30 rounded-lg transition-colors"
                                                title="Buka Link">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </a>
                                        @endif

                                        <!-- Assign ke Prestasi -->
                                        <button
                                            @click="openAssignModal({{ $sk->id }}, '{{ addslashes($sk->sk_number) }}', '{{ addslashes($sk->title) }}')"
                                            class="p-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg transition-colors"
                                            title="Assign ke Prestasi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                            </svg>
                                        </button>

                                        <!-- Hapus -->
                                        <form action="{{ route('admin.sk.destroy', $sk) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Yakin ingin menghapus SK ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                title="{{ $sk->assignments_count > 0 ? 'SK tidak dapat dihapus karena sudah di-assign' : 'Hapus' }}"
                                                @if($sk->assignments_count > 0) disabled @endif>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-gray-500 dark:text-gray-400">Belum ada SK yang diupload</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($skDocuments->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $skDocuments->links() }}
                </div>
            @endif
        </div>

        <!-- Upload SK Modal -->
        <div x-show="showModal" x-cloak @click.self="showModal = false" @keydown.escape.window="showModal = false"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div @click.stop x-show="showModal" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">

                <!-- Modal Header -->
                <div
                    class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between z-10">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Upload SK Baru</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pilih metode upload SK</p>
                    </div>
                    <button @click="showModal = false"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form action="{{ route('admin.sk.store') }}" method="POST" enctype="multipart/form-data"
                    class="p-6 space-y-5">
                    @csrf

                    <!-- SK Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                            Nomor SK <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="sk_number" value="{{ old('sk_number') }}"
                            placeholder="Contoh: 123/SK/UNPATTI/2024" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('sk_number') border-red-500 @enderror">
                        @error('sk_number')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                            Judul SK <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}"
                            placeholder="Contoh: SK Prestasi Mahasiswa Semester Genap 2024" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('title') border-red-500 @enderror">
                        @error('title')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Upload Type Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-3">
                            Metode Upload <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="uploadType = 'file'"
                                :class="uploadType === 'file' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600'"
                                class="px-4 py-3 border-2 rounded-lg font-medium transition-all hover:scale-105 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                Upload File PDF
                            </button>
                            <button type="button" @click="uploadType = 'link'"
                                :class="uploadType === 'link' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600'"
                                class="px-4 py-3 border-2 rounded-lg font-medium transition-all hover:scale-105 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                Masukkan Link
                            </button>
                        </div>
                        <input type="hidden" name="upload_type" :value="uploadType">
                    </div>

                    <!-- File Upload -->
                    <div x-show="uploadType === 'file'" x-cloak>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                            File SK (PDF) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="file" name="sk_file" accept=".pdf" @change="handleFileSelect($event)"
                                class="hidden" id="sk_file_input">
                            <label for="sk_file_input"
                                :class="fileName ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-purple-400'"
                                class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl cursor-pointer transition-colors">
                                <div class="flex flex-col items-center justify-center">
                                    <svg :class="fileName ? 'text-green-500' : 'text-gray-400'" class="w-10 h-10 mb-2"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p :class="fileName ? 'text-green-700 dark:text-green-300 font-medium' : 'text-gray-500 dark:text-gray-400'"
                                        class="text-sm">
                                        <span x-show="!fileName">Klik untuk pilih file PDF</span>
                                        <span x-show="fileName" x-text="fileName"></span>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">Maksimal 10MB</p>
                                </div>
                            </label>
                        </div>
                        @error('sk_file')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Link Input -->
                    <div x-show="uploadType === 'link'" x-cloak>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                            Link Eksternal <span class="text-red-500">*</span>
                        </label>
                        <input type="url" name="external_link" value="{{ old('external_link') }}"
                            placeholder="https://example.com/sk-document.pdf"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('external_link') border-red-500 @enderror">
                        @error('external_link')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date & Issuer -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                                Tanggal Terbit <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="issued_date" value="{{ old('issued_date') }}" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('issued_date') border-red-500 @enderror">
                            @error('issued_date')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                                Penerbit SK <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="issued_by" value="{{ old('issued_by') }}"
                                placeholder="Contoh: Rektor Universitas Pattimura" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('issued_by') border-red-500 @enderror">
                            @error('issued_by')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                            Catatan (Opsional)
                        </label>
                        <textarea name="notes" rows="3" placeholder="Catatan tambahan tentang SK ini"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" @click="showModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan SK
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assign Modal -->
        <div x-show="showAssignModal" x-cloak @click.self="closeAssignModal()" @keydown.escape.window="closeAssignModal()"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div @click.stop x-show="showAssignModal" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden flex flex-col">

                <!-- Modal Header -->
                <div
                    class="bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 border-b border-purple-200 dark:border-purple-800 px-6 py-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-purple-600 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Assign SK ke Prestasi</h3>
                                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1"
                                    x-text="'SK: ' + selectedSk.number"></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400" x-text="selectedSk.title"></p>
                            </div>
                        </div>
                        <button @click="closeAssignModal()"
                            class="p-2 hover:bg-white/50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Search Bar -->
                    <div class="mb-4">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" x-model="searchQuery" @keydown.enter.prevent="fetchAchievements()"
                                class="w-full pl-10 pr-4 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                placeholder="Ketik NIM, nama mahasiswa, atau nama lomba lalu tekan Enter...">
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1 italic">* Tekan Enter untuk mulai mencari</p>
                    </div>

                    <div x-show="loading" class="flex items-center justify-center py-12">
                        <svg class="animate-spin h-8 w-8 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>

                    <!-- Initial Content / No Results -->
                    <div x-show="!loading && achievements.length === 0" class="text-center py-12">
                        <div x-show="!searchQuery" class="animate-in fade-in zoom-in duration-300">
                            <div
                                class="w-20 h-20 bg-purple-50 dark:bg-purple-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-purple-600 dark:text-purple-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 font-bold text-lg">Mulai Cari Prestasi</p>
                            <p class="text-gray-500 dark:text-gray-400 mt-2 max-w-sm mx-auto">Masukkan NIM, nama mahasiswa,
                                atau nama lomba pada kolom pencarian di bawah dan tekan Enter.</p>
                        </div>

                        <div x-show="searchQuery" class="animate-in fade-in zoom-in duration-300">
                            <div
                                class="w-20 h-20 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 font-bold text-lg">Hasil Tidak Ditemukan</p>
                            <p class="text-gray-500 dark:text-gray-400 mt-2">Maaf, kami tidak menemukan prestasi yang cocok
                                dengan kata kunci "<span x-text="searchQuery" class="font-semibold"></span>".</p>
                        </div>
                    </div>

                    <div x-show="!loading && achievements.length > 0">
                        <!-- Action Buttons -->
                        <div class="flex gap-2 mb-4">
                            <button type="button" @click="selectAll()"
                                class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg flex items-center gap-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                Pilih Semua
                            </button>
                            <button type="button" @click="deselectAll()"
                                class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg flex items-center gap-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Batal Pilih
                            </button>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left w-12">
                                            <input type="checkbox" @change="toggleAll($event.target.checked)"
                                                :checked="selectedAchievements.length === achievements.length && achievements.length > 0"
                                                class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600">
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            NIM</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Nama Mahasiswa</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Nama Lomba</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Kategori</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Tingkat</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Tanggal Event</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="achievement in achievements" :key="achievement.sa_id">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="px-4 py-3">
                                                <input type="checkbox" :value="achievement.sa_id"
                                                    @change="toggleAchievement(achievement.sa_id)"
                                                    :checked="selectedAchievements.includes(achievement.sa_id)"
                                                    class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600">
                                            </td>
                                            <td class="px-4 py-3 text-gray-900 dark:text-white font-medium"
                                                x-text="achievement.student.student_id"></td>
                                            <td class="px-4 py-3 text-gray-900 dark:text-white"
                                                x-text="achievement.student.name"></td>
                                            <td class="px-4 py-3 text-gray-900 dark:text-white"
                                                x-text="achievement.event_name"></td>
                                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400"
                                                x-text="achievement.achievement.category.name"></td>
                                            <td class="px-4 py-3">
                                                <span
                                                    class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full"
                                                    x-text="achievement.level"></span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400"
                                                x-text="formatDate(achievement.event_date)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Notes -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Catatan (Opsional)
                            </label>
                            <textarea x-model="notes" rows="2"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                placeholder="Catatan untuk assignment ini"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div x-show="!loading && achievements.length > 0"
                    class="border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">
                                <span class="font-bold text-purple-600 dark:text-purple-400"
                                    x-text="selectedAchievements.length"></span> prestasi dipilih
                            </span>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="closeAssignModal()"
                                class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg flex items-center gap-2 transition-colors font-medium">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Batal
                            </button>
                            <button type="button" @click="submitAssignment()"
                                :disabled="selectedAchievements.length === 0 || submitting"
                                class="px-4 py-2.5 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-lg flex items-center gap-2 transition-colors font-medium">
                                <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <svg x-show="submitting" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span x-text="submitting ? 'Memproses...' : 'Assign SK & Approve Prestasi'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function skManagement() {
            return {
                showModal: {{ $errors->any() ? 'true' : 'false' }},
                uploadType: '{{ old('upload_type', 'file') }}',
                fileName: '',
                showAssignModal: false,
                selectedSk: {},
                achievements: [],
                selectedAchievements: [],
                searchQuery: '',
                notes: '',
                loading: false,
                submitting: false,

                async fetchAchievements() {
                    if (this.loading) return;

                    this.loading = true;
                    try {
                        const url = `/admin/sk/${this.selectedSk.id}/achievements?q=${encodeURIComponent(this.searchQuery)}`;
                        const response = await fetch(url);
                        const data = await response.json();
                        this.achievements = data.achievements || [];

                        // Clear selections that are no longer in the results? 
                        // Usually better to keep them if they were already selected, 
                        // but for simplicity we keep selectedAchievements as is.
                    } catch (error) {
                        console.error('Error fetching achievements:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.fileName = file.name;
                    } else {
                        this.fileName = '';
                    }
                },

                async openAssignModal(skId, skNumber, skTitle) {
                    this.selectedSk = { id: skId, number: skNumber, title: skTitle };
                    this.showAssignModal = true;
                    this.achievements = [];
                    this.selectedAchievements = [];
                    this.searchQuery = '';
                    this.notes = '';
                },

                closeAssignModal() {
                    this.showAssignModal = false;
                    this.selectedSk = {};
                    this.achievements = [];
                    this.selectedAchievements = [];
                    this.searchQuery = '';
                    this.notes = '';
                },

                toggleAchievement(saId) {
                    const index = this.selectedAchievements.indexOf(saId);
                    if (index > -1) {
                        this.selectedAchievements.splice(index, 1);
                    } else {
                        this.selectedAchievements.push(saId);
                    }
                },

                toggleAll(checked) {
                    if (checked) {
                        this.selectedAchievements = [...new Set([...this.selectedAchievements, ...this.achievements.map(a => a.sa_id)])];
                    } else {
                        // Only deselect what is currently visible
                        const visibleIds = this.achievements.map(a => a.sa_id);
                        this.selectedAchievements = this.selectedAchievements.filter(id => !visibleIds.includes(id));
                    }
                },

                selectAll() {
                    this.selectedAchievements = [...new Set([...this.selectedAchievements, ...this.achievements.map(a => a.sa_id)])];
                },

                deselectAll() {
                    this.selectedAchievements = [];
                },

                async submitAssignment() {
                    if (this.selectedAchievements.length === 0) {
                        alert('Pilih minimal 1 prestasi');
                        return;
                    }

                    if (!confirm(`Assign SK ke ${this.selectedAchievements.length} prestasi dan approve semuanya?`)) {
                        return;
                    }

                    this.submitting = true;

                    try {
                        const formData = new FormData();
                        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                        this.selectedAchievements.forEach(id => {
                            formData.append('achievement_ids[]', id);
                        });
                        if (this.notes) {
                            formData.append('notes', this.notes);
                        }

                        const response = await fetch(`/admin/sk/${this.selectedSk.id}/process-assignment`, {
                            method: 'POST',
                            body: formData
                        });

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            alert('Gagal memproses assignment');
                        }
                    } catch (error) {
                        console.error('Error submitting assignment:', error);
                        alert('Terjadi kesalahan saat memproses assignment');
                    } finally {
                        this.submitting = false;
                    }
                },

                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
                }
            }
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection