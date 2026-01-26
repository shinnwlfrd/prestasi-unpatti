@extends('layouts.validator')

@section('title', 'Detail SK')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('validator.sk.index') }}" 
               class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
               title="Kembali">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Detail Surat Keputusan (SK)</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $sk->sk_number }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- SK Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi SK</h3>
                
                <dl class="space-y-3">
                    <div class="flex">
                        <dt class="w-40 text-sm font-medium text-gray-500 dark:text-gray-400">Nomor SK</dt>
                        <dd class="flex-1 text-sm text-gray-900 dark:text-white font-semibold">{{ $sk->sk_number }}</dd>
                    </div>
                    <div class="flex">
                        <dt class="w-40 text-sm font-medium text-gray-500 dark:text-gray-400">Judul</dt>
                        <dd class="flex-1 text-sm text-gray-900 dark:text-white">{{ $sk->title }}</dd>
                    </div>
                    <div class="flex">
                        <dt class="w-40 text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Terbit</dt>
                        <dd class="flex-1 text-sm text-gray-900 dark:text-white">{{ $sk->issued_date->format('d F Y') }}</dd>
                    </div>
                    <div class="flex">
                        <dt class="w-40 text-sm font-medium text-gray-500 dark:text-gray-400">Penerbit</dt>
                        <dd class="flex-1 text-sm text-gray-900 dark:text-white">{{ $sk->issued_by }}</dd>
                    </div>
                    <div class="flex">
                        <dt class="w-40 text-sm font-medium text-gray-500 dark:text-gray-400">Tipe Dokumen</dt>
                        <dd class="flex-1 text-sm">
                            @if($sk->file_type === 'file')
                                <span class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full">File PDF</span>
                            @elseif($sk->file_type === 'link')
                                <span class="px-2 py-1 text-xs bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400 rounded-full">Link Eksternal</span>
                            @endif
                        </dd>
                    </div>
                    @if($sk->notes)
                    <div class="flex">
                        <dt class="w-40 text-sm font-medium text-gray-500 dark:text-gray-400">Catatan</dt>
                        <dd class="flex-1 text-sm text-gray-900 dark:text-white">{{ $sk->notes }}</dd>
                    </div>
                    @endif
                    <div class="flex">
                        <dt class="w-40 text-sm font-medium text-gray-500 dark:text-gray-400">Dibuat Oleh</dt>
                        <dd class="flex-1 text-sm text-gray-900 dark:text-white">{{ $sk->creator->name }} ({{ $sk->created_at->format('d/m/Y H:i') }})</dd>
                    </div>
                    <div class="flex">
                        <dt class="w-40 text-sm font-medium text-gray-500 dark:text-gray-400">Prestasi Ter-assign</dt>
                        <dd class="flex-1 text-sm">
                            <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full">{{ $sk->assignments->count() }} prestasi</span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Prestasi List -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Prestasi yang Ter-assign</h3>
                </div>
                <div class="p-6">
                    @if($sk->assignments->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">NIM</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nama Mahasiswa</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nama Lomba</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kategori</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tingkat</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Di-assign Oleh</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($sk->assignments as $assignment)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $assignment->achievement->student->nim }}</td>
                                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $assignment->achievement->student->name }}</td>
                                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $assignment->achievement->event_name }}</td>
                                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $assignment->achievement->achievement->category->name }}</td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full">{{ $assignment->achievement->level }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $assignment->assignedBy->name }}</td>
                                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $assignment->assigned_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                            SK ini belum di-assign ke prestasi manapun
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aksi</h3>
                
                <div class="space-y-3">
                    @if($sk->file_type === 'file')
                        <a href="{{ route('validator.sk.preview', $sk) }}" 
                           target="_blank" 
                           class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center justify-center gap-2 transition-colors font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Lihat Dokumen
                        </a>
                    @elseif($sk->file_type === 'link')
                        <a href="{{ $sk->external_link }}" 
                           target="_blank" 
                           class="w-full px-4 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg flex items-center justify-center gap-2 transition-colors font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Buka Link
                        </a>
                    @endif

                    <a href="{{ route('validator.sk.assign', $sk) }}" 
                       class="w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center justify-center gap-2 transition-colors font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        Assign ke Prestasi
                    </a>

                    <a href="{{ route('validator.sk.index') }}" 
                       class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg flex items-center justify-center gap-2 transition-colors font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </div>

                <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <strong>Info:</strong> Anda dapat menggunakan SK ini saat approve prestasi mahasiswa.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
