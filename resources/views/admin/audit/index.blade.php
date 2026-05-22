@extends('layouts.admin')

@section('title', 'Audit Log Konfigurasi')

@section('content')
    <div class="space-y-6 lg:space-y-8 px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Audit Log Konfigurasi</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Lacak dan pantau perubahan konfigurasi akademik, tingkat prestasi, dan kategori.</p>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.audit-logs') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search Input -->
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari Operator/IP..."
                            class="w-full pl-8 pr-4 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Action Filter -->
                    <select name="action" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                        <option value="">Semua Aksi</option>
                        <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Dibuat (Created)</option>
                        <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Diperbarui (Updated)</option>
                        <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Dihapus (Deleted)</option>
                        <option value="restored" {{ request('action') == 'restored' ? 'selected' : '' }}>Dipulihkan (Restored)</option>
                        <option value="activated" {{ request('action') == 'activated' ? 'selected' : '' }}>Diaktifkan (Activated)</option>
                    </select>

                    <!-- Type Filter -->
                    <select name="type" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                        <option value="">Semua Modul</option>
                        <option value="period" {{ request('type') == 'period' ? 'selected' : '' }}>Periode Akademik</option>
                        <option value="level" {{ request('type') == 'level' ? 'selected' : '' }}>Level Prestasi</option>
                        <option value="category" {{ request('type') == 'category' ? 'selected' : '' }}>Kategori Prestasi</option>
                    </select>

                    <!-- Buttons -->
                    <div class="flex items-center gap-2">
                        <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm w-full md:w-auto">
                            Filter
                        </button>
                        @if(request()->hasAny(['search', 'action', 'type']))
                            <a href="{{ route('admin.audit-logs') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 w-full md:w-auto text-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Audit Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm" x-data="{ selectedLog: null }">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Modul</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">User / IP</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Perubahan</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition-colors">
                                <!-- Timestamp -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $log->created_at->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('H:i:s') }}</div>
                                </td>

                                <!-- Auditable Type -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $log->auditable_name }}
                                    </span>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $log->auditable_id }}</div>
                                </td>

                                <!-- Action Badge -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-{{ $log->action_badge }}-100 text-{{ $log->action_badge }}-800 dark:bg-{{ $log->action_badge }}-900/30 dark:text-{{ $log->action_badge }}-400 uppercase">
                                        {{ $log->action_label }}
                                    </span>
                                </td>

                                <!-- User Name / IP Address -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $log->user_name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $log->ip_address ?? '-' }}</div>
                                </td>

                                <!-- Changed Fields summary -->
                                <td class="px-6 py-4 max-w-md">
                                    @if($log->changed_fields)
                                        <div class="text-xs space-y-1.5">
                                            @foreach($log->changed_fields as $field => $change)
                                                @if(in_array($field, ['password', 'remember_token']))
                                                    <div><span class="font-semibold text-gray-700 dark:text-gray-300 font-mono">{{ $field }}</span>: <span class="text-gray-400">[Dihasildan/Disembunyikan]</span></div>
                                                @else
                                                    <div class="truncate">
                                                        <span class="font-semibold text-gray-700 dark:text-gray-300 font-mono">{{ $field }}</span>:
                                                        <span class="text-red-500 line-through mr-1">{{ is_array($change['old']) ? json_encode($change['old']) : $change['old'] ?? 'null' }}</span>
                                                        <span class="text-green-600 font-medium">&rarr; {{ is_array($change['new']) ? json_encode($change['new']) : $change['new'] ?? 'null' }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @elseif($log->action === 'created')
                                        <span class="text-xs text-green-600 dark:text-green-400 italic">Data konfigurasi baru dibuat</span>
                                    @elseif($log->action === 'deleted')
                                        <span class="text-xs text-red-500 dark:text-red-400 italic">Data konfigurasi dihapus</span>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500 italic">Tidak ada perubahan field</span>
                                    @endif
                                </td>

                                <!-- Detail Modal trigger -->
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <button @click="selectedLog = {{ json_encode($log) }}"
                                        class="text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 p-1.5 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tidak ada audit log</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Perubahan konfigurasi akan tercatat secara otomatis di sini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $logs->links() }}
                </div>
            @endif

            <!-- Modal Detail JSON -->
            <div x-show="selectedLog" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" @keydown.escape.window="selectedLog = null">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="selectedLog = null"></div>

                    <!-- Modal content -->
                    <div class="inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 sm:my-8 sm:align-middle">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Detail Perubahan Konfigurasi</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Melihat snapshot lengkap data lama dan baru.</p>
                            </div>
                            <button @click="selectedLog = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="p-6 space-y-6">
                            <!-- Info Panel -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 dark:bg-gray-900/20 p-4 rounded-lg text-xs">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400 block mb-1">Modul</span>
                                    <span class="font-semibold text-gray-900 dark:text-white" x-text="selectedLog?.auditable_name"></span>
                                    <span class="text-gray-400 dark:text-gray-500" x-text="'(ID: ' + selectedLog?.auditable_id + ')'"></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400 block mb-1">Aksi</span>
                                    <span class="font-semibold text-gray-900 dark:text-white uppercase" x-text="selectedLog?.action"></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400 block mb-1">Operator</span>
                                    <span class="font-semibold text-gray-900 dark:text-white" x-text="selectedLog?.user_name"></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400 block mb-1">Alamat IP / Browser</span>
                                    <span class="font-mono text-gray-900 dark:text-white" x-text="selectedLog?.ip_address ?? '-'"></span>
                                    <span class="text-gray-400 dark:text-gray-500 block truncate" x-text="selectedLog?.user_agent ?? '-'"></span>
                                </div>
                            </div>

                            <!-- Side-by-side JSON/Diff -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Old values -->
                                <div>
                                    <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                        Nilai Sebelumnya (Old)
                                    </h4>
                                    <div class="bg-gray-900 rounded-lg p-4 overflow-auto max-h-96 text-xs text-green-400 font-mono">
                                        <pre x-text="selectedLog?.old_values ? JSON.stringify(selectedLog.old_values, null, 2) : 'Tidak ada data (Baru dibuat)'"></pre>
                                    </div>
                                </div>

                                <!-- New values -->
                                <div>
                                    <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                        Nilai Baru (New)
                                    </h4>
                                    <div class="bg-gray-900 rounded-lg p-4 overflow-auto max-h-96 text-xs text-green-400 font-mono">
                                        <pre x-text="selectedLog?.new_values ? JSON.stringify(selectedLog.new_values, null, 2) : 'Tidak ada data (Dihapus)'"></pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 text-right">
                            <button type="button" @click="selectedLog = null"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
