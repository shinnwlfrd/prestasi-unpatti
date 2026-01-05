@extends('layouts.admin')
@section('title', 'Daftar Mahasiswa')
@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">NIM</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Nama</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Fakultas</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Prodi</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Semester</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">IPK</th>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Prestasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($students as $s)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">{{ $s->student_id }}</td>
                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $s->name }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $s->faculty }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $s->program_study }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $s->semester }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $s->gpa }}</td>
                    <td class="px-4 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">{{ $s->student_achievements_count }}</span></td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $students->links() }}</div>
</div>
@endsection
