@extends('layouts.admin')
@section('title', 'Prestasi Mahasiswa')
@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Mahasiswa</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Event</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Jenis</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Level</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Tanggal</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Status</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Validator</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($achievements as $a)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800 dark:text-white">{{ $a->student->name ?? '-' }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $a->student->student_id ?? '-' }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $a->event_name }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $a->achievement->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $a->level }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($a->event_date)->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        @php $sc = match($a->validation_status) { 'Disetujui' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400', 'Ditolak' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', default => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' }; @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $sc }}">{{ $a->validation_status }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $a->validator->name ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $achievements->links() }}</div>
</div>
@endsection
