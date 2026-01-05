@extends('layouts.admin')
@section('title', 'Log Validasi')
@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Mahasiswa</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Prestasi</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Status Lama</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Status Baru</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Validator</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Waktu</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $log->studentAchievement->student->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $log->studentAchievement->event_name ?? '-' }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-1 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">{{ $log->old_status }}</span></td>
                    <td class="px-4 py-3">
                        @php $sc = match($log->new_status) { 'Disetujui' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400', 'Ditolak' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', default => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' }; @endphp
                        <span class="px-2 py-1 rounded text-xs {{ $sc }}">{{ $log->new_status }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $log->validator->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $log->validated_at ? \Carbon\Carbon::parse($log->validated_at)->format('d M Y H:i') : '-' }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 max-w-xs truncate">{{ $log->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $logs->links() }}</div>
</div>
@endsection
