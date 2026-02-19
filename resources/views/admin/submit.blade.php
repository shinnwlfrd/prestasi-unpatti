@extends('layouts.admin')

@section('title', 'Ajukan Prestasi Mahasiswa')

@section('content')
    <div class="space-y-6 px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs/Back -->
        <nav class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-purple-600 transition-colors">Dashboard</a>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-purple-600 font-medium">Ajukan Prestasi</span>
        </nav>

        <!-- Header Section -->
        <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 p-5 sm:p-6 md:p-8 shadow-sm">
            <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-purple-500/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-48 h-48 bg-indigo-500/5 rounded-full blur-2xl"></div>

            <div class="relative flex flex-col md:flex-row md:items-center gap-6">
                <div class="flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl shadow-lg shadow-purple-200 dark:shadow-none flex items-center justify-center transform hover:scale-105 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white leading-tight">Ajukan Prestasi Mahasiswa</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1 max-w-xl">Administrator dapat mengajukan prestasi atas nama mahasiswa secara kolektif dengan otoritas penuh.</p>
                </div>
            </div>
        </div>

        <!-- Main Form -->
        <form action="{{ route('admin.submit.store') }}" method="POST" enctype="multipart/form-data" 
              class="space-y-8"
              x-data="{ 
                loading: false, 
                action: '{{ old('submit_action', 'pending') }}',
                students: []
              }" 
              @students-changed="students = $event.detail"
              @submit="loading = true">
            @csrf

            <!-- Section 1: Identitas & Kategori -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden group">
                <div class="bg-gray-50/50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-xs">Identitas & Kategori</h3>
                </div>
                <div class="p-4 sm:p-6 space-y-5 sm:space-y-6">
                    <!-- Student Selection -->
                    <div class="relative">
                        <x-student-search-select 
                            name="student_ids" 
                            :required="true" 
                            :error="$errors->first('student_ids')" 
                            :multiple="true"
                        />
                    </div>

                    <!-- Kategori Prestasi -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                            Kategori Prestasi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="category_id" required
                                class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 dark:text-white rounded-2xl py-4 px-5 text-base font-medium focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 transition-all shadow-sm @error('category_id') border-red-500 @enderror">
                                <option value="">Pilih Kategori Prestasi</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('category_id')
                            <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1 font-medium italic">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Detail Kegiatan & Kompetisi -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden group">
                <div class="bg-gray-50/50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-xs">Detail Kompetisi</h3>
                </div>
                <div class="p-4 sm:p-6 space-y-5 sm:space-y-6">
                    <!-- Event Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                            Nama Lomba/Event <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="event_name" value="{{ old('event_name') }}" required
                            class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 dark:text-white rounded-2xl py-4 px-5 text-base font-medium focus:ring-4 focus:ring-purple-500/10 transition-all shadow-sm @error('event_name') border-red-500 @enderror placeholder:text-gray-400"
                            placeholder="Contoh: Olimpiade Matematika Nasional 2024">
                        @error('event_name')
                            <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Level Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                            Tingkat <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach($levels as $level)
                                @if($level->is_active)
                                    <label class="relative group/level">
                                        <input type="radio" name="level" value="{{ $level->name }}" {{ old('level') == $level->name ? 'checked' : '' }}
                                            required class="peer sr-only">
                                        <div class="px-5 py-4 rounded-xl border-2 border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-center cursor-pointer transition-all hover:bg-white dark:hover:bg-gray-700 hover:border-purple-200 dark:hover:border-purple-800/50 peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 peer-checked:shadow-md peer-checked:shadow-purple-100/50 dark:peer-checked:shadow-none">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="text-sm font-bold text-gray-800 dark:text-gray-200 peer-checked:text-purple-700 dark:peer-checked:text-purple-400">
                                                    {{ $level->name }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-purple-500 rounded-full flex items-center justify-center opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all shadow-sm z-10">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Organizer -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                            Penyelenggara <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="organizer" value="{{ old('organizer') }}" required
                            class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 dark:text-white rounded-2xl py-4 px-5 text-base font-medium focus:ring-4 focus:ring-purple-500/10 transition-all shadow-sm @error('organizer') border-red-500 @enderror"
                            placeholder="Contoh: Universitas Gadjah Mada atau Kemendikbudristek">
                        @error('organizer')
                            <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Event Date & Ranking in Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                Tanggal Pelaksanaan <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="date" name="event_date" value="{{ old('event_date') }}" required
                                    class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 dark:text-white rounded-2xl py-4 px-5 text-base font-medium focus:ring-4 focus:ring-purple-500/10 transition-all shadow-sm @error('event_date') border-red-500 @enderror">
                            </div>
                            @error('event_date')
                                <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                Peringkat/Ranking <span class="text-gray-400 font-normal text-xs ml-1">(Opsional)</span>
                            </label>
                            <input type="text" name="ranking" value="{{ old('ranking') }}"
                                class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 dark:text-white rounded-2xl py-4 px-5 text-base font-medium focus:ring-4 focus:ring-purple-500/10 transition-all shadow-sm"
                                placeholder="Contoh: Juara 1, Best Speaker, atau Finalis">
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                            Deskripsi <span class="text-gray-400 font-normal text-xs ml-1">(Opsional)</span>
                        </label>
                        <textarea name="description" rows="4"
                            class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 dark:text-white rounded-2xl py-4 px-5 text-base font-medium focus:ring-4 focus:ring-purple-500/10 transition-all shadow-sm placeholder:text-gray-400"
                            placeholder="Tambahkan detail tambahan jika diperlukan...">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 3: Berkas Mahasiswa (Dinamis per Mahasiswa) -->
        <div class="space-y-8">
            @if($errors->has('attachments.*'))
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-4 animate-in fade-in duration-300">
                    <div class="flex items-center gap-3 text-red-600 dark:text-red-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-xs font-bold uppercase tracking-widest">Ada masalah dengan berkas yang Anda unggah. Pastikan semua sertifikat telah dipilih.</p>
                    </div>
                </div>
            @endif

            <template x-if="students.length === 0">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-dashed border-gray-300 dark:border-gray-700 p-12 text-center">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 font-medium">Pilih mahasiswa di atas untuk mulai mengunggah sertifikat dan berkas pendukung.</p>
                    </div>
                </template>

                <template x-for="(student, sIndex) in students" :key="student.student_id">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden animate-in slide-in-from-top-4 duration-300">
                        <!-- Student Header in Card -->
                        <div class="bg-purple-50 dark:bg-purple-900/20 px-6 py-4 border-b border-purple-100 dark:border-purple-800 flex items-center gap-4">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-800 rounded-xl flex items-center justify-center text-purple-600 dark:text-purple-400 font-bold text-sm" x-text="sIndex + 1"></div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-900 dark:text-white truncate" x-text="student.name"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="student.student_id + ' • ' + student.faculty"></p>
                            </div>
                        </div>

                        <div class="p-4 sm:p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Certificate Upload for this student -->
                            <div class="flex flex-col" x-data="{ 
                                fileName: '', 
                                fileSize: '', 
                                fileType: '',
                                fileUrl: '',
                                showPreview: false
                            }">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Sertifikat Utama <span class="text-red-500">*</span></label>
                                <div class="relative group/upload h-full min-h-[120px] sm:min-h-[140px]">
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
                                    <label :for="'cert-' + student.student_id" class="h-full flex flex-col items-center justify-center border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl bg-gray-50/30 dark:bg-gray-800/20 hover:bg-purple-50/30 dark:hover:bg-purple-900/10 hover:border-purple-300 dark:hover:border-purple-700 cursor-pointer transition-all p-6 text-center text-center">
                                        <div x-show="!fileName">
                                            <svg class="w-8 h-8 text-purple-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                            </svg>
                                            <p class="text-xs font-bold text-gray-700 dark:text-gray-300">Pilih Sertifikat</p>
                                        </div>
                                        <div x-show="fileName" class="animate-in fade-in zoom-in duration-300">
                                            <p class="text-[11px] font-bold text-gray-900 dark:text-white truncate max-w-[180px]" x-text="fileName"></p>
                                            <p class="text-[9px] text-purple-600 font-bold mt-1" x-text="fileSize"></p>
                                            <button type="button" @click.stop.prevent="showPreview = true" 
                                                class="mt-2 text-[9px] font-bold text-purple-600 uppercase tracking-widest border-b border-purple-500/30 hover:border-purple-500">
                                                Pratinjau
                                            </button>
                                        </div>
                                    </label>
                                </div>

                                <!-- Preview Modal within loop context or global -->
                                <div x-show="showPreview" x-cloak
                                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                                    @click.self="showPreview = false">
                                    <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl">
                                        <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                            <p class="font-bold text-gray-900 dark:text-white" x-text="fileName"></p>
                                            <button type="button" @click="showPreview = false" class="p-2 hover:bg-purple-100 dark:hover:bg-purple-700 rounded-full">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                        <div class="p-6 bg-gray-50 dark:bg-gray-900/50" style="height: 65vh;">
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

                            <!-- Supporting Documents for this student -->
                            <div class="flex flex-col" x-data="{ 
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
                                            name: file.name
                                        });
                                        this.dataTransfer.items.add(file);
                                    });
                                    this.sync();
                                    event.target.value = '';
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
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Dokumen Pendukung <span class="text-gray-400 font-normal lowercase">(opsional, maks 2)</span></label>

                                <input type="file" :name="'attachments[' + student.student_id + '][additional_documents][]'" x-ref="hiddenInput" multiple class="hidden">

                                <div class="relative mb-4" x-show="allFiles.length < maxFiles">
                                    <input type="file" accept=".pdf,.jpg,.jpeg,.png" multiple @change="addSupporting"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                    <div class="bg-white dark:bg-gray-800 border-2 border-dashed border-purple-100 dark:border-purple-800 rounded-2xl p-4 text-center hover:border-purple-400 transition-all">
                                        <p class="text-[10px] font-bold text-purple-600 uppercase">Tambah Berkas</p>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <template x-for="f in allFiles" :key="f.id">
                                        <div class="flex items-center gap-2 p-2 bg-purple-50/50 dark:bg-purple-900/10 rounded-xl border border-purple-100 dark:border-purple-900/30">
                                            <div class="flex-1 min-w-0 text-[10px] font-bold text-gray-700 dark:text-gray-300 truncate" x-text="f.name"></div>
                                            <button type="button" @click="removeSupporting(f.id)" class="p-1 hover:text-red-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Section 4: Validasi & Tindakan -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-purple-100 dark:border-purple-900 shadow-xl shadow-purple-500/5 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-indigo-700 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-white uppercase tracking-wider text-xs">Penetapan Status & Otoritas</h3>
                    </div>
                </div>

                <div class="p-8 space-y-8">
                    <!-- Action Tabs/Radios -->
                    <div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="submit_action" value="pending" x-model="action" class="peer sr-only">
                                <div class="h-full p-5 rounded-3xl border-2 border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 transition-all group-hover:border-amber-200 dark:group-hover:border-gray-600 peer-checked:border-amber-500 peer-checked:bg-amber-50/30 dark:peer-checked:bg-amber-900/10 peer-checked:shadow-lg peer-checked:shadow-amber-500/10 flex flex-col items-center text-center">
                                    <div class="w-12 h-12 bg-amber-50 dark:bg-amber-900/30 rounded-2xl flex items-center justify-center mb-4 transition-colors">
                                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest peer-checked:text-amber-700">Pending</span>
                                </div>
                                <div class="absolute inset-0 rounded-3xl ring-2 ring-amber-500 opacity-0 scale-105 peer-checked:opacity-100 peer-checked:scale-100 transition-all pointer-events-none"></div>
                            </label>

                            <label class="relative cursor-pointer group">
                                <input type="radio" name="submit_action" value="approve" x-model="action" class="peer sr-only">
                                <div class="h-full p-5 rounded-3xl border-2 border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 transition-all group-hover:border-emerald-200 dark:group-hover:border-gray-600 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/30 dark:peer-checked:bg-emerald-900/10 peer-checked:shadow-lg peer-checked:shadow-emerald-500/10 flex flex-col items-center text-center text-center">
                                    <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/30 rounded-2xl flex items-center justify-center mb-4 transition-colors">
                                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest peer-checked:text-emerald-700">Approve</span>
                                </div>
                                <div class="absolute inset-0 rounded-3xl ring-2 ring-emerald-500 opacity-0 scale-105 peer-checked:opacity-100 peer-checked:scale-100 transition-all pointer-events-none"></div>
                            </label>

                        </div>
                    </div>

                    <!-- Conditional Approve Fields -->
                    <div x-show="action === 'approve'" x-cloak 
                        class="space-y-6 animate-in slide-in-from-top-4 duration-500">


                        <!-- SK Selection -->
                        <div class="space-y-3 animate-in fade-in duration-300">
                            <x-sk-search-select 
                                name="sk_id" 
                                :error="$errors->first('sk_id')"
                                :selected="old('sk_id') ? $skDocuments->firstWhere('id', old('sk_id')) : null"
                            />
                            @if($skDocuments->isEmpty())
                                <div class="p-4 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                    <p class="text-xs text-gray-500 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Belum ada data SK tersedia
                                    </p>
                                    <a href="{{ route('admin.sk.index') }}" target="_blank" class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest hover:underline">Tambah SK</a>
                                </div>
                            @endif
                        </div>
                    <!-- Info Banner -->
                    <div class="p-5 bg-gradient-to-r from-purple-50 to-purple-100/30 dark:from-purple-950/20 dark:to-purple-900/10 rounded-3xl border border-purple-100 dark:border-purple-800/50">
                        <div class="flex gap-4">
                            <div class="w-10 h-10 bg-white dark:bg-gray-800 rounded-2xl shadow-sm flex items-center justify-center flex-shrink-0 animate-bounce-subtle">
                                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-purple-900 dark:text-purple-300">Otoritas Admin</p>
                                <p class="text-[11px] text-purple-800/70 dark:text-purple-400 leading-relaxed mt-1">
                                    Sebagai Admin, Anda memiliki hak untuk memotong jalur birokrasi dan langsung menyetujui prestasi. Pastikan data akurat karena tindakan ini akan langsung mempengaruhi poin prestasi mahasiswa.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Bottom Actions -->
            <div class="sticky bottom-0 sm:bottom-4 z-40 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md px-4 sm:px-8 py-4 rounded-none sm:rounded-3xl border-t sm:border border-gray-200 dark:border-gray-700 shadow-2xl flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <div class="hidden sm:block">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest leading-none">Status Formulir</p>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></div>
                        <span class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-tight" x-text="action === 'pending' ? 'Siap Simpan (Pending)' : 'Siap Terbitkan (Setujui)'"></span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex-1 sm:flex-none px-8 py-3 rounded-2xl border-2 border-gray-100 dark:border-gray-700 text-sm font-bold text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-700 transition-all text-center uppercase tracking-widest">
                        Batal
                    </a>
                    <button type="submit" :disabled="loading"
                        class="flex-1 sm:flex-none w-full px-10 py-3 bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 active:scale-95 text-white rounded-2xl text-sm font-bold shadow-xl shadow-purple-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-3 group">
                        <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="uppercase tracking-widest" x-text="loading ? 'Memproses...' : 'Ajukan Prestasi'"></span>
                        <svg x-show="!loading" class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        @keyframes pulse-subtle {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(0.98); }
        }
        .animate-pulse-subtle {
            animation: pulse-subtle 3s infinite ease-in-out;
        }
        @keyframes bounce-subtle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }
        .animate-bounce-subtle {
            animation: bounce-subtle 2s infinite ease-in-out;
        }
    </style>

    @stack('scripts')
@endsection
