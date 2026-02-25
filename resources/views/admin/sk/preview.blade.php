@extends('layouts.admin')

@section('title', 'Preview SK - ' . $sk->sk_number)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Preview Surat Keputusan</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Nomor: {{ $sk->sk_number }}
                </p>
            </div>
            <div class="flex gap-2">
                @if($sk->file_path)
                <a href="{{ Storage::url($sk->file_path) }}" 
                   target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Lihat Dokumen
                </a>
                @endif
                <button onclick="window.history.back()" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </button>
            </div>
        </div>
    </div>

    <!-- SK Information Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Informasi SK</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor SK</label>
                <p class="text-gray-900">{{ $sk->sk_number }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal SK</label>
                <p class="text-gray-900">{{ $sk->sk_date->format('d F Y') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Periode Akademik</label>
                <p class="text-gray-900">{{ $sk->academicPeriod->name ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $sk->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ ucfirst($sk->status) }}
                </span>
            </div>
            @if($sk->description)
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <p class="text-gray-900">{{ $sk->description }}</p>
            </div>
            @endif
            @if($sk->file_path)
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">File Dokumen</label>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ Storage::url($sk->file_path) }}" 
                       target="_blank"
                       class="text-blue-600 hover:text-blue-800 hover:underline">
                        {{ basename($sk->file_path) }}
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Statistics Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Statistik Prestasi</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-sm font-medium text-blue-600 mb-1">Total Prestasi</div>
                <div class="text-2xl font-bold text-blue-900">{{ $sk->assignments->count() }}</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <div class="text-sm font-medium text-green-600 mb-1">Mahasiswa</div>
                <div class="text-2xl font-bold text-green-900">{{ $sk->assignments->pluck('achievement.student_id')->unique()->count() }}</div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
                <div class="text-sm font-medium text-purple-600 mb-1">Total Poin</div>
                <div class="text-2xl font-bold text-purple-900">{{ $sk->assignments->sum('achievement.points') }}</div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4">
                <div class="text-sm font-medium text-orange-600 mb-1">Fakultas</div>
                <div class="text-2xl font-bold text-orange-900">{{ $sk->assignments->pluck('achievement.student.faculty')->unique()->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Achievements List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Daftar Prestasi yang Terdaftar</h2>
            <p class="mt-1 text-sm text-gray-600">
                {{ $sk->assignments->count() }} prestasi terdaftar dalam SK ini
            </p>
        </div>

        @if($sk->assignments->isEmpty())
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada prestasi</h3>
            <p class="mt-1 text-sm text-gray-500">SK ini belum memiliki prestasi yang terdaftar.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            No
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Mahasiswa
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Prestasi
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kategori
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tingkat
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Poin
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($sk->assignments as $index => $assignment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $index + 1 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $assignment->achievement->student->name }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $assignment->achievement->student->student_id }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $assignment->achievement->student->faculty }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                {{ $assignment->achievement->achievement->name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ Str::limit($assignment->achievement->description, 50) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $assignment->achievement->achievement->category->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($assignment->achievement->level->name === 'Internasional') bg-purple-100 text-purple-800
                                @elseif($assignment->achievement->level->name === 'Nasional') bg-green-100 text-green-800
                                @elseif($assignment->achievement->level->name === 'Provinsi') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $assignment->achievement->level->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            {{ $assignment->achievement->points }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $assignment->achievement->achievement_date->format('d/m/Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- PDF Preview Section (if file exists) -->
    @if($sk->file_path && Storage::exists($sk->file_path))
    <div class="mt-6 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Preview Dokumen</h2>
        </div>
        <div class="p-6">
            <div class="aspect-[8.5/11] bg-gray-100 rounded-lg overflow-hidden">
                <iframe 
                    src="{{ Storage::url($sk->file_path) }}" 
                    class="w-full h-full"
                    frameborder="0">
                </iframe>
            </div>
            <div class="mt-4 text-center">
                <a href="{{ Storage::url($sk->file_path) }}" 
                   target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Dokumen
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
