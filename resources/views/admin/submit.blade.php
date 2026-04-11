@php /** @var \Illuminate\Support\ViewErrorBag $errors */ @endphp
@extends('layouts.admin')

@section('title', 'Ajukan Prestasi Mahasiswa')

@section('content')
    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-32">
        {{-- Breadcrumb --}}
        <nav class="flex text-sm text-gray-500 dark:text-gray-400 mb-6">
            <ol class="flex items-center space-x-2">
                <li><a href="{{ route('admin.dashboard') }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Dashboard</a></li>
                <li>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </li>
                <li class="text-primary-600 dark:text-primary-400 font-medium">Ajukan Prestasi</li>
            </ol>
        </nav>

        {{-- Hero Banner --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-4 sm:p-6 mb-6 sm:mb-8 flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left gap-4 relative overflow-hidden transition-colors duration-200">
            <div class="absolute right-0 top-0 w-64 h-full bg-gradient-to-l from-primary-500/5 to-transparent pointer-events-none"></div>
            <div class="w-12 h-12 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="relative z-10">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-1">Ajukan Prestasi Mahasiswa</h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-2xl">Administrator dapat mengajukan prestasi atas nama mahasiswa secara kolektif dengan otoritas penuh.</p>
            </div>
        </div>

        {{-- Main Form --}}
        <form action="{{ route('admin.submit.store') }}" method="POST" enctype="multipart/form-data"
              class="space-y-6"
              x-data="{ 
                loading: false, 
                action: '{{ old('submit_action', 'pending') }}',
                students: {!! $selectedStudentsJson !!}
              }" 
              @students-changed.window="students = $event.detail"
              @submit="loading = true">
            @csrf

            {{-- Section 1: Identitas & Kategori --}}
            <section class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 transition-colors duration-200">
                <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-gray-200 dark:border-gray-800 flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center text-[10px] font-bold">1</div>
                    <h3 class="font-bold text-xs sm:text-sm uppercase tracking-wider text-gray-900 dark:text-white">Identitas & Kategori</h3>
                </div>
                <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                    {{-- Student Selection --}}
                    <div class="relative">
                        <x-siakad-student-select 
                            name="student_ids" 
                            :required="true" 
                            :error="$errors->first('student_ids')" 
                            :multiple="true"
                            :selected="$selectedStudentsJson"
                        />
                    </div>

                    {{-- Kategori Prestasi --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Kategori Prestasi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="category_id" required
                                class="w-full pl-4 pr-10 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm appearance-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all dark:text-white @error('category_id') border-red-500 @enderror">
                                <option value="" disabled selected>Pilih Kategori Prestasi</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <svg class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        @error('category_id')
                            <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1 font-medium">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Section 2: Detail Kompetisi --}}
            <section class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 transition-colors duration-200">
                <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-gray-200 dark:border-gray-800 flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center text-[10px] font-bold">2</div>
                    <h3 class="font-bold text-xs sm:text-sm uppercase tracking-wider text-gray-900 dark:text-white">Detail Kompetisi</h3>
                </div>
                <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                    {{-- Event Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nama Lomba/Event <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="event_name" value="{{ old('event_name') }}" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all dark:text-white @error('event_name') border-red-500 @enderror"
                            placeholder="Contoh: Olimpiade Matematika Nasional 2024">
                        @error('event_name')
                            <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Level Selection --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tingkat <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            @foreach($levels as $level)
                                @if($level->is_active)
                                    <label class="cursor-pointer relative">
                                        <input type="radio" name="level" value="{{ $level->name }}" {{ old('level') == $level->name ? 'checked' : '' }}
                                            required class="peer sr-only">
                                        <div class="w-full px-4 py-3 text-sm font-medium text-center rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 peer-checked:border-primary-500 peer-checked:bg-primary-50 dark:peer-checked:bg-primary-900/20 peer-checked:text-primary-600 dark:peer-checked:text-primary-400 transition-all text-gray-700 dark:text-gray-300">
                                            {{ $level->name }}
                                        </div>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                        @error('level')
                            <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Organizer --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Penyelenggara <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="organizer" value="{{ old('organizer') }}" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all dark:text-white @error('organizer') border-red-500 @enderror"
                            placeholder="Contoh: Universitas Gadjah Mada atau Kemendikbudristek">
                        @error('organizer')
                            <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Event Date & Ranking --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tanggal Pelaksanaan <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="event_date" value="{{ old('event_date') }}" required
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all dark:text-white @error('event_date') border-red-500 @enderror">
                            @error('event_date')
                                <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex justify-between">
                                <span>Peringkat/Ranking</span>
                                <span class="text-gray-400 font-normal text-xs">(Opsional)</span>
                            </label>
                            <input type="text" name="ranking" value="{{ old('ranking') }}"
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all dark:text-white"
                                placeholder="Contoh: Juara 1, Best Speaker, atau Finalis">
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex justify-between">
                            <span>Deskripsi</span>
                            <span class="text-gray-400 font-normal text-xs">(Opsional)</span>
                        </label>
                        <textarea name="description" rows="3"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all resize-y dark:text-white"
                            placeholder="Tambahkan detail tambahan jika diperlukan...">{{ old('description') }}</textarea>
                    </div>
                </div>
            </section>

            {{-- Section 3: Berkas Mahasiswa (Dynamic per Student) --}}
            <div class="space-y-6">
                @if($errors->has('attachments.*'))
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                        <div class="flex items-center gap-3 text-red-600 dark:text-red-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <p class="text-xs font-bold uppercase tracking-widest">Ada masalah dengan berkas yang Anda unggah. Pastikan semua sertifikat telah dipilih.</p>
                        </div>
                    </div>
                @endif

                <template x-if="students.length === 0">
                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-12 text-center">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 font-medium">Pilih mahasiswa di atas untuk mulai mengunggah sertifikat dan berkas pendukung.</p>
                    </div>
                </template>

                <template x-for="(student, sIndex) in students" :key="student.student_id">
                    <section class="bg-primary-50/50 dark:bg-primary-950/20 rounded-xl shadow-sm border border-primary-200/50 dark:border-primary-800/30 transition-colors duration-200 overflow-hidden">
                        {{-- Student Header --}}
                        <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-primary-100 dark:border-primary-900/30 bg-white/50 dark:bg-gray-900/50 flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full bg-primary-200/50 dark:bg-primary-800/40 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-sm" x-text="sIndex + 1"></div>
                            <div>
                                <h4 class="font-bold text-sm text-primary-700 dark:text-primary-300" x-text="student.name"></h4>
                                <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">
                                    <span x-text="student.nim"></span> • <span x-text="student.prodi"></span>
                                    <br class="sm:hidden"><span class="sm:before:content-['•_'] italic" x-text="student.faculty"></span>
                                </p>
                            </div>
                        </div>

                        {{-- Upload Area --}}
                        <div class="p-4 sm:p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Certificate Upload --}}
                                <div class="bg-white dark:bg-gray-900 rounded-lg p-5 border border-gray-200 dark:border-gray-700" x-data="{ 
                                    fileName: '', 
                                    fileSize: '', 
                                    fileType: '',
                                    fileUrl: '',
                                    showPreview: false
                                }">
                                    <label class="block text-sm font-medium mb-4 uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                        Sertifikat Utama <span class="text-red-500">*</span>
                                    </label>
                                    <input type="file" :name="'attachments[' + student.student_id + '][certificate]'" accept=".pdf,.jpg,.jpeg,.png" required class="hidden" :id="'cert-' + student.student_id"
                                        @change="
                                            const file = $event.target.files[0];
                                            if (file) {
                                                fileName = file.name;
                                                fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                                                fileUrl = URL.createObjectURL(file);
                                                fileType = file.type;
                                            }
                                        ">
                                    <label :for="'cert-' + student.student_id" class="border-2 border-dashed border-primary-300/50 dark:border-primary-700/50 rounded-lg p-6 sm:p-8 flex flex-col items-center justify-center text-center bg-gray-50 dark:bg-gray-800 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors cursor-pointer group">
                                        <div x-show="!fileName">
                                            <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-3 mx-auto group-hover:scale-110 transition-transform">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                            </div>
                                            <span class="text-sm font-semibold text-primary-600 dark:text-primary-400">Pilih Sertifikat</span>
                                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">PDF, JPG, PNG (Max. 2MB)</p>
                                        </div>
                                        <div x-show="fileName" class="text-center">
                                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate max-w-[200px]" x-text="fileName"></p>
                                            <p class="text-xs text-primary-600 dark:text-primary-400 font-medium mt-1" x-text="fileSize"></p>
                                            <button type="button" @click.stop.prevent="showPreview = true" 
                                                class="mt-3 text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 px-4 py-1.5 rounded-full uppercase tracking-wider transition-colors">
                                                Pratinjau
                                            </button>
                                        </div>
                                    </label>

                                    {{-- Certificate Preview Modal --}}
                                    <div x-show="showPreview" x-cloak
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                                        @click.self="showPreview = false">
                                        <div class="bg-white dark:bg-gray-900 rounded-xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl">
                                            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                                <p class="font-bold text-gray-900 dark:text-white" x-text="fileName"></p>
                                                <button type="button" @click="showPreview = false" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            <div class="p-6 bg-gray-50 dark:bg-gray-800" style="height: 65vh;">
                                                <template x-if="fileType.startsWith('image/')">
                                                    <img :src="fileUrl" class="max-w-full mx-auto rounded-lg shadow-lg">
                                                </template>
                                                <template x-if="fileType === 'application/pdf'">
                                                    <iframe :src="fileUrl" class="w-full h-full rounded-lg" frameborder="0"></iframe>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Supporting Documents Upload --}}
                                <div class="bg-white dark:bg-gray-900 rounded-lg p-5 border border-gray-200 dark:border-gray-700 flex flex-col" x-data="{ 
                                    allFiles: [],
                                    dataTransfer: new DataTransfer(),
                                    maxFiles: 2,

                                    addSupporting(event) {
                                        const newFiles = Array.from(event.target.files);
                                        const remaining = this.maxFiles - this.allFiles.length;
                                        if (newFiles.length > remaining) {
                                            alert('Maksimal 2 berkas pendukung per mahasiswa.');
                                            event.target.value = '';
                                            return;
                                        }
                                        newFiles.forEach(file => {
                                            this.allFiles.push({
                                                id: Math.random().toString(36).substr(2, 9),
                                                file: file,
                                                name: file.name,
                                                url: URL.createObjectURL(file),
                                                type: file.type
                                            });
                                            this.dataTransfer.items.add(file);
                                        });
                                        this.sync();
                                        event.target.value = '';
                                    },
                                    previewFile: null,
                                    showPreview(f) {
                                        this.previewFile = f;
                                    },
                                    removeSupporting(id) {
                                        const idx = this.allFiles.findIndex(f => f.id === id);
                                        if (idx !== -1) {
                                            this.allFiles.splice(idx, 1);
                                            this.dataTransfer = new DataTransfer();
                                            this.allFiles.forEach(f => this.dataTransfer.items.add(f.file));
                                            this.sync();
                                        }
                                    },
                                    sync() {
                                        this.$refs.hiddenInput.files = this.dataTransfer.files;
                                    }
                                }">
                                    <label class="block text-sm font-medium mb-4 uppercase tracking-wide text-gray-700 dark:text-gray-300 flex justify-between">
                                        <span>Dokumen Pendukung</span>
                                        <span class="text-gray-400 font-normal text-xs normal-case">(opsional, maks 2)</span>
                                    </label>

                                    <input type="file" :name="'attachments[' + student.student_id + '][additional_documents][]'" x-ref="hiddenInput" multiple class="hidden">

                                    {{-- Upload drop zone --}}
                                    <div class="relative flex-1" x-show="allFiles.length < maxFiles">
                                        <input type="file" accept=".pdf,.jpg,.jpeg,.png" multiple @change="addSupporting"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                        <div class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg p-6 sm:p-8 flex flex-col items-center justify-center text-center flex-1 hover:border-primary-500/50 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer group h-full min-h-[100px] sm:min-h-[120px]">
                                            <span class="text-[10px] sm:text-xs font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider group-hover:underline">Tambah Berkas</span>
                                        </div>
                                    </div>

                                    {{-- File list --}}
                                    <div class="space-y-2 mt-3" x-show="allFiles.length > 0">
                                        <template x-for="f in allFiles" :key="f.id">
                                            <div class="flex items-center gap-2 p-2 bg-primary-50/50 dark:bg-primary-900/10 rounded-lg border border-primary-100 dark:border-primary-900/30 group/item">
                                                <div class="flex-1 min-w-0 text-xs font-bold text-gray-700 dark:text-gray-300 truncate" x-text="f.name"></div>
                                                <div class="flex items-center gap-1 opacity-0 group-hover/item:opacity-100 transition-opacity">
                                                    <button type="button" @click="showPreview(f)" class="p-1 text-primary-600 hover:bg-primary-100 dark:hover:bg-primary-900/40 rounded-lg transition-colors" title="Pratinjau">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    </button>
                                                    <button type="button" @click="removeSupporting(f.id)" class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/40 rounded-lg transition-colors" title="Hapus">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Supporting Documents Preview Modal --}}
                                    <div x-show="previewFile" x-cloak
                                        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                                        @click.self="previewFile = null">
                                        <div class="bg-white dark:bg-gray-900 rounded-xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl">
                                            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800">
                                                <div class="flex items-center gap-3">
                                                    <div class="p-2 bg-primary-100 dark:bg-primary-900/40 rounded-lg">
                                                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    </div>
                                                    <p class="font-bold text-gray-900 dark:text-white text-sm truncate max-w-xs sm:max-w-md" x-text="previewFile ? previewFile.name : ''"></p>
                                                </div>
                                                <button type="button" @click="previewFile = null" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full transition-colors">
                                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            <div class="p-4 sm:p-6 bg-gray-100 dark:bg-gray-800 flex items-center justify-center" style="height: 70vh;">
                                                <template x-if="previewFile && previewFile.type.startsWith('image/')">
                                                    <div class="w-full h-full flex items-center justify-center p-2 bg-white dark:bg-gray-900 rounded-xl shadow-inner overflow-auto">
                                                        <img :src="previewFile.url" class="max-w-full max-h-full object-contain">
                                                    </div>
                                                </template>
                                                <template x-if="previewFile && previewFile.type === 'application/pdf'">
                                                    <iframe :src="previewFile.url" class="w-full h-full rounded-xl shadow-lg border-0 bg-white" title="PDF Preview"></iframe>
                                                </template>
                                                <template x-if="previewFile && !previewFile.type.startsWith('image/') && previewFile.type !== 'application/pdf'">
                                                    <div class="text-center p-8">
                                                        <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center mx-auto mb-4 text-amber-600">
                                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                        </div>
                                                        <p class="text-gray-600 dark:text-gray-400 font-bold uppercase tracking-widest text-xs">Format file tidak mendukung pratinjau langsung</p>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </template>
            </div>

            {{-- Section 4: Status Selection --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="cursor-pointer relative">
                    <input type="radio" name="submit_action" value="pending" x-model="action" class="peer sr-only">
                    <div class="bg-white dark:bg-gray-900 rounded-xl border-2 border-gray-200 dark:border-gray-700 p-4 flex flex-col items-center justify-center text-center shadow-sm relative overflow-hidden transition-all peer-checked:border-amber-500 peer-checked:shadow-lg peer-checked:shadow-amber-500/10">
                        <div class="absolute inset-0 bg-amber-500/0 peer-checked:bg-amber-500/5 transition-colors"></div>
                        <svg class="w-6 h-6 text-amber-500 mb-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-900 dark:text-white relative z-10">Pending</span>
                    </div>
                </label>
                <label class="cursor-pointer relative">
                    <input type="radio" name="submit_action" value="approve" x-model="action" class="peer sr-only">
                    <div class="bg-white dark:bg-gray-900 rounded-xl border-2 border-gray-200 dark:border-gray-700 p-4 flex flex-col items-center justify-center text-center shadow-sm relative overflow-hidden transition-all peer-checked:border-emerald-500 peer-checked:shadow-lg peer-checked:shadow-emerald-500/10" :class="action !== 'approve' ? 'opacity-50 grayscale' : ''">
                        <div class="absolute inset-0 bg-emerald-500/0 peer-checked:bg-emerald-500/5 transition-colors"></div>
                        <svg class="w-6 h-6 text-emerald-500 mb-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-900 dark:text-white relative z-10">Approve</span>
                    </div>
                </label>
            </div>

            {{-- Conditional Approve Fields --}}
            <div x-show="action === 'approve'" x-cloak class="space-y-6">
                {{-- SK Selection --}}
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6 space-y-4">
                    <x-sk-search-select 
                        name="sk_id" 
                        :error="$errors->first('sk_id')"
                        :selected="old('sk_id') ? $skDocuments->firstWhere('id', old('sk_id')) : null"
                    />
                    @if($skDocuments->isEmpty())
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Belum ada data SK tersedia
                            </p>
                            <a href="{{ route('admin.sk.index') }}" target="_blank" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest hover:underline">Tambah SK</a>
                        </div>
                    @endif
                </div>

                {{-- Info Banner --}}
                <div class="p-5 bg-primary-50 dark:bg-primary-950/20 rounded-xl border border-primary-100 dark:border-primary-800/50">
                    <div class="flex gap-4">
                        <div class="w-10 h-10 bg-white dark:bg-gray-900 rounded-xl shadow-sm flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-primary-900 dark:text-primary-300">Otoritas Admin</p>
                            <p class="text-xs text-primary-800/70 dark:text-primary-400 leading-relaxed mt-1">
                                Sebagai Admin, Anda memiliki hak untuk memotong jalur birokrasi dan langsung menyetujui prestasi. Pastikan data akurat karena tindakan ini akan langsung mempengaruhi poin prestasi mahasiswa.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sticky Bottom Actions --}}
            <div class="fixed bottom-0 left-0 lg:left-64 xl:left-72 right-0 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border-t border-gray-200 dark:border-gray-800 p-3 sm:p-4 px-4 sm:px-6 flex items-center justify-between z-40 transition-all">
                <div class="flex items-center gap-2">
                    <span class="hidden sm:inline text-[10px] font-black text-gray-400 uppercase tracking-widest">Status Formulir:</span>
                    <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2 py-1 rounded-lg text-[9px] sm:text-[10px] font-black"
                          :class="action === 'pending' ? 'bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300' : 'bg-emerald-100 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300'">
                        <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                        <span x-text="action === 'pending' ? 'DRAFT PENDING' : 'SIAP APPROVE'"></span>
                    </span>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="px-3 sm:px-5 py-2 text-[10px] sm:text-xs font-black text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors uppercase tracking-widest">
                        BATAL
                    </a>
                    <button type="submit" :disabled="loading"
                        class="px-4 sm:px-6 py-2 sm:py-2.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-[10px] sm:text-xs font-black rounded-xl shadow-xl shadow-gray-900/20 dark:shadow-none flex items-center gap-2 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed uppercase tracking-widest">
                        <svg x-show="loading" class="w-3 h-3 sm:w-4 sm:h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="loading ? 'PROSES...' : 'AJUKAN'"></span>
                        <svg x-show="!loading" class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    @stack('scripts')
@endsection
