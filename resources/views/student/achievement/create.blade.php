@extends('layouts.app')

@section('title', 'Ajukan Prestasi')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-fade-in pb-12">
    <!-- Breadcrumbs/Back -->
    <nav class="flex items-center gap-2 text-sm">
        <a href="{{ route('student.dashboard') }}" class="text-gray-500 hover:text-indigo-600 transition-colors">Dashboard</a>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-indigo-600 font-medium">Ajukan Prestasi</span>
    </nav>

    <!-- Header Section -->
    <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 p-8 shadow-sm">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-indigo-500/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-48 h-48 bg-blue-500/5 rounded-full blur-2xl"></div>
        
        <div class="relative flex flex-col md:flex-row md:items-center gap-6">
            <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl shadow-lg shadow-indigo-200 dark:shadow-none flex items-center justify-center transform hover:scale-105 transition-transform">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white leading-tight">Ajukan Prestasi Baru</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 max-w-xl">Laporkan pencapaian membanggakan Anda untuk diverifikasi dan mendapatkan pengakuan resmi dari universitas.</p>
            </div>
        </div>
    </div>

    <!-- Main Form -->
    <form action="{{ route('student.achievement.store') }}" method="POST" enctype="multipart/form-data" 
          class="space-y-8"
          x-data="{ loading: false }" 
          @submit="loading = true">
        @csrf
        
        <!-- Section 1: Kategori -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden group">
            <div class="bg-gray-50/50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3">
                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-xs">Kategori Prestasi</h3>
            </div>
            <div class="p-6">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                    Pilih Kategori <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select name="category_id" required
                        class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 dark:text-white rounded-2xl py-4 px-5 text-base font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm @error('category_id') border-red-500 @enderror">
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

        <!-- Section 2: Detail Kegiatan -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden group">
            <div class="bg-gray-50/50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3">
                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-xs">Detail Kompetisi</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Event Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                        Nama Lomba/Kegiatan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="event_name" value="{{ old('event_name') }}" required
                        class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 dark:text-white rounded-2xl py-4 px-5 text-base font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm @error('event_name') border-red-500 @enderror placeholder:text-gray-400"
                        placeholder="Contoh: Juara 1 Lomba Karya Tulis Ilmiah Nasional">
                    @error('event_name')
                        <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level Selection -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                        Tingkat <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($levels as $level)
                            @if($level->is_active)
                                <label class="relative group/level">
                                    <input type="radio" name="level" value="{{ $level->name }}" {{ old('level') == $level->name ? 'checked' : '' }}
                                        required class="peer sr-only">
                                    <div class="px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-center cursor-pointer transition-all hover:bg-white dark:hover:bg-gray-700 hover:border-indigo-200 dark:hover:border-indigo-800 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/20 peer-checked:shadow-sm">
                                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200 peer-checked:text-indigo-700 dark:peer-checked:text-indigo-400 uppercase tracking-tight">
                                            {{ $level->name }}
                                        </span>
                                    </div>
                                    <div class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-indigo-500 rounded-full flex items-center justify-center opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all shadow-sm z-10">
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
                        class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 dark:text-white rounded-2xl py-4 px-5 text-base font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm @error('organizer') border-red-500 @enderror"
                        placeholder="Contoh: Kemendikbudristek / Universitas Indonesia">
                    @error('organizer')
                        <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date & Ranking -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                            Tanggal Pelaksanaan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="event_date" value="{{ old('event_date') }}" required
                            class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 dark:text-white rounded-2xl py-4 px-5 text-base font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm @error('event_date') border-red-500 @enderror">
                        @error('event_date')
                            <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                            Peringkat/Ranking <span class="text-gray-400 font-normal text-xs ml-1">(Opsional)</span>
                        </label>
                        <input type="text" name="ranking" value="{{ old('ranking') }}"
                            class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 dark:text-white rounded-2xl py-4 px-5 text-base font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm"
                            placeholder="Contoh: Juara 1, Finalis, atau Peserta">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                        Deskripsi <span class="text-gray-400 font-normal text-xs ml-1">(Opsional)</span>
                    </label>
                    <textarea name="description" rows="4"
                        class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 dark:text-white rounded-2xl py-4 px-5 text-base font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm placeholder:text-gray-400"
                        placeholder="Ceritakan sedikit tentang pencapaian Anda...">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Section 3: Berkas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Certificate Upload Area -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col group">
                <div class="bg-gray-50/50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-xs">Sertifikat Utama <span class="text-red-500">*</span></h3>
                    </div>
                </div>
                
                <div class="p-6 flex-1 flex flex-col justify-center" x-data="{ 
                    fileName: '', 
                    fileSize: '', 
                    fileUrl: '',
                    fileType: '',
                    showPreview: false
                }">
                    <div class="relative group/upload h-full min-h-[160px]">
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
                        
                        <label for="certificate" class="h-full flex flex-col items-center justify-center border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl bg-gray-50/50 dark:bg-gray-800/50 hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 hover:border-indigo-300 dark:hover:border-indigo-700 cursor-pointer transition-all p-8 text-center">
                            <div x-show="!fileName" class="flex flex-col items-center animate-pulse-subtle">
                                <div class="w-16 h-16 bg-white dark:bg-gray-700 rounded-2xl shadow-sm flex items-center justify-center mb-4 group-hover/upload:scale-110 transition-transform">
                                    <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">Pilih Berkas Sertifikat</p>
                                <p class="text-[10px] text-gray-500 mt-1 uppercase tracking-widest font-semibold">PDF, JPG, PNG &bull; MAKS 2MB</p>
                            </div>
                            
                            <div x-show="fileName" class="flex flex-col items-center animate-in fade-in zoom-in duration-300">
                                <template x-if="fileType.startsWith('image/')">
                                    <div class="relative mb-3 group/preview">
                                        <img :src="fileUrl" class="w-20 h-20 object-cover rounded-xl shadow-md border-2 border-white dark:border-gray-700">
                                        <div class="absolute inset-0 bg-black/40 rounded-xl flex items-center justify-center opacity-0 group-hover/preview:opacity-100 transition-opacity">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="fileType === 'application/pdf'">
                                    <div class="w-16 h-16 bg-red-50 dark:bg-red-900/20 rounded-2xl flex items-center justify-center mb-3">
                                        <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                                            <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                                        </svg>
                                    </div>
                                </template>
                                <p class="text-xs font-bold text-gray-900 dark:text-white truncate max-w-[200px]" x-text="fileName"></p>
                                <p class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold mt-1" x-text="fileSize"></p>
                                
                                <button type="button" @click.stop.prevent="showPreview = true" 
                                    class="mt-3 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest border-b-2 border-indigo-500/30 hover:border-indigo-500 transition-all pb-0.5">
                                    Pratinjau Berkas
                                </button>
                            </div>
                        </label>
                    </div>

                    <!-- Preview Modal -->
                    <div x-show="showPreview" x-cloak
                        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                        @click.self="showPreview = false">
                        <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl animate-in zoom-in duration-300">
                            <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                <p class="font-bold text-gray-900 dark:text-white" x-text="fileName"></p>
                                <button type="button" @click="showPreview = false" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="p-6 overflow-auto bg-gray-50 dark:bg-gray-900/50" style="height: 70vh;">
                                <template x-if="fileType.startsWith('image/')">
                                    <img :src="fileUrl" class="max-w-full mx-auto rounded-lg shadow-lg">
                                </template>
                                <template x-if="fileType === 'application/pdf'">
                                    <iframe :src="fileUrl" class="w-full h-full rounded-lg" frameborder="0"></iframe>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    @error('certificate')
                        <p class="text-red-500 text-xs mt-2 font-medium italic">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Additional Documents Area -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col group">
                <div class="bg-gray-50/50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-xs">Dokumen Pendukung <span class="text-gray-400 font-normal lowercase tracking-normal">(opsional)</span></h3>
                    </div>
                </div>
                
                <div class="p-6 flex-1 bg-indigo-50/20 dark:bg-indigo-900/5"
                    x-data="{ 
                        allFiles: [],
                        dataTransfer: new DataTransfer(),
                        maxFiles: 2,
                        errorMessage: '',
                        
                        addFiles(event) {
                            this.errorMessage = '';
                            const newFiles = Array.from(event.target.files);
                            const remainingSlots = this.maxFiles - this.allFiles.length;
                            
                            if (newFiles.length > remainingSlots) {
                                this.errorMessage = `Maksimal hanya ${this.maxFiles} file tambahan.`;
                                event.target.value = '';
                                return;
                            }

                            newFiles.forEach(file => {
                                if (!this.allFiles.some(f => f.name === file.name && f.size === file.size)) {
                                    this.allFiles.push({
                                        id: Math.random().toString(36).substr(2, 9),
                                        file: file,
                                        name: file.name,
                                        size: (file.size / 1024 / 1024).toFixed(2) + ' MB'
                                    });
                                    this.dataTransfer.items.add(file);
                                }
                            });
                            this.syncInput();
                            event.target.value = '';
                        },
                        
                        removeFile(id) {
                            const index = this.allFiles.findIndex(f => f.id === id);
                            if (index !== -1) {
                                this.allFiles.splice(index, 1);
                                this.rebuildDataTransfer();
                                this.errorMessage = '';
                            }
                        },
                        
                        rebuildDataTransfer() {
                            this.dataTransfer = new DataTransfer();
                            this.allFiles.forEach(f => this.dataTransfer.items.add(f.file));
                            this.syncInput();
                        },
                        
                        syncInput() {
                            this.$refs.hiddenInput.files = this.dataTransfer.files;
                        }
                    }">
                    
                    <input type="file" name="additional_documents[]" x-ref="hiddenInput" multiple class="hidden">
                    
                    <!-- Upload Tool -->
                    <div class="relative mb-6" x-show="allFiles.length < maxFiles">
                        <input type="file" accept=".pdf,.jpg,.jpeg,.png" multiple @change="addFiles"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div class="group/plus bg-white dark:bg-gray-800 border-2 border-dashed border-indigo-200 dark:border-indigo-800 rounded-2xl p-6 text-center transition-all hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10">
                            <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl mx-auto flex items-center justify-center mb-2 group-hover/plus:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <p class="text-[10px] font-bold text-gray-900 dark:text-white uppercase tracking-widest">Tambah Dokumen</p>
                        </div>
                    </div>

                    <!-- File Items -->
                    <div class="space-y-3">
                        <template x-for="file in allFiles" :key="file.id">
                            <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-xl border border-indigo-100 dark:border-indigo-900 shadow-sm animate-in slide-in-from-left duration-200">
                                <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="file.name"></p>
                                    <p class="text-[10px] text-gray-500 font-medium" x-text="file.size"></p>
                                </div>
                                <button type="button" @click="removeFile(file.id)" class="p-2 hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-400 hover:text-red-500 transition-colors rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <!-- Empty State -->
                        <div x-show="allFiles.length === 0" class="flex flex-col items-center justify-center py-6 text-gray-400 text-center opacity-60">
                            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <p class="text-xs font-medium uppercase tracking-widest leading-relaxed">Belum ada<br>berkas tambahan</p>
                        </div>
                    </div>

                    @error('additional_documents')
                        <p class="text-red-500 text-xs mt-2 font-medium italic">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Sticky Bottom Actions -->
        <div class="sticky bottom-4 z-40 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md px-8 py-4 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl flex items-center justify-between gap-4 animate-in slide-in-from-bottom-8 duration-500">
            <div class="hidden sm:block">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest leading-none">Formulir Pengajuan</p>
                <div class="flex items-center gap-2 mt-1">
                    <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                    <span class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-tight">Menunggu Pengajuan</span>
                </div>
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <a href="{{ route('student.dashboard') }}" 
                   class="flex-1 sm:flex-none px-8 py-3 rounded-2xl border-2 border-gray-100 dark:border-gray-700 text-sm font-bold text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-700 transition-all text-center uppercase tracking-widest">
                    Batal
                </a>
                <button type="submit" :disabled="loading"
                    class="flex-1 sm:flex-none px-10 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 active:scale-95 text-white rounded-2xl text-sm font-bold shadow-xl shadow-indigo-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-3 group">
                    <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="uppercase tracking-widest" x-text="loading ? 'Memproses...' : 'Ajukan Sekarang'"></span>
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
</style>

@endsection
