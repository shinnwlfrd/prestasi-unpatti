@extends('layouts.admin')

@section('title', 'Jenis Prestasi')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Jenis Prestasi</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Daftar kategori prestasi mahasiswa</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" 
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Kategori
        </a>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($categories as $category)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $category->name }}</h3>
                    @if($category->description)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $category->description }}</p>
                    @endif
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $category->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400' }}">
                    {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>

            <div class="flex items-center gap-2 mt-4">
                <a href="{{ route('admin.categories.edit', $category) }}" 
                    class="flex-1 px-3 py-2 text-center bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors text-sm font-medium">
                    Edit
                </a>
                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="flex-1" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-3 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors text-sm font-medium">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada kategori prestasi</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
