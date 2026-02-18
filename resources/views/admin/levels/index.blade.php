@extends('layouts.admin')

@section('title', 'Kelola Level Prestasi')

@section('content')
    <div class="space-y-6"
        x-data="{ showModal: false, editMode: false, levelId: null, levelName: '', levelDescription: '', levelPoints: '' }">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Level Prestasi</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola level prestasi mahasiswa</p>
            </div>
            <button @click="showModal = true; editMode = false; levelName = ''; levelDescription = ''; levelPoints = ''"
                class="w-full sm:w-auto px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium flex items-center justify-center gap-2 transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Level
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Nama</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Deskripsi</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Poin</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Status</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($levels as $level)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $level->name }}</td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $level->description ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 rounded text-xs font-medium">
                                        {{ $level->points }} poin
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($level->is_active)
                                        <span
                                            class="px-2 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded text-xs font-medium">Aktif</span>
                                    @else
                                        <span
                                            class="px-2 py-1 bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400 rounded text-xs font-medium">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="showModal = true; editMode = true; levelId = {{ $level->id }}; levelName = '{{ $level->name }}'; levelDescription = '{{ $level->description }}'; levelPoints = '{{ $level->points }}'"
                                            class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form action="{{ route('admin.levels.destroy', $level) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus level ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
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
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada level
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($levels->hasPages())
            <div class="mt-4">
                {{ $levels->links() }}
            </div>
        @endif

        <!-- Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="showModal = false"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <!-- Center modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    <form
                        :action="editMode ? '{{ route('admin.levels.index') }}/' + levelId : '{{ route('admin.levels.store') }}'"
                        method="POST">
                        @csrf
                        <input type="hidden" name="_method" x-bind:value="editMode ? 'PUT' : 'POST'">

                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center gap-4 mb-4">
                                <div
                                    class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 dark:bg-purple-900/30">
                                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white"
                                        x-text="editMode ? 'Edit Level' : 'Tambah Level'"></h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Isi informasi level prestasi</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- Nama -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Nama Level <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" x-model="levelName" required
                                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500"
                                        placeholder="Contoh: Internasional, Nasional, Universitas">
                                </div>

                                <!-- Deskripsi -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Deskripsi
                                    </label>
                                    <textarea name="description" x-model="levelDescription" rows="3"
                                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500"
                                        placeholder="Deskripsi singkat tentang level ini..."></textarea>
                                </div>

                                <!-- Poin -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Poin <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="points" x-model="levelPoints" required min="0" step="1"
                                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500"
                                        placeholder="Contoh: 100">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Poin yang didapat mahasiswa
                                        untuk prestasi level ini</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                                <span x-text="editMode ? 'Update' : 'Simpan'"></span>
                            </button>
                            <button type="button" @click="showModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection