@extends('layouts.admin')
@section('title', 'Jenis Prestasi')
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Tambah Jenis Prestasi</h3>
        <form action="{{ route('admin.achievements.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama</label>
                <input type="text" name="name" required class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kategori</label>
                <select name="category" required class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white">
                    <option value="Akademik">Akademik</option>
                    <option value="Non-akademik">Non-akademik</option>
                </select>
            </div>
            <button type="submit" class="w-full py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg">Tambah</button>
        </form>
    </div>
    <!-- List -->
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Nama</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Kategori</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($types as $t)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $t->name }}</td>
                        <td class="px-4 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $t->category == 'Akademik' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' }}">{{ $t->category }}</span></td>
                        <td class="px-4 py-3">
                            <form action="{{ route('admin.achievements.delete', $t) }}" method="POST" onsubmit="return confirm('Hapus?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
