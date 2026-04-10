@extends('layouts.admin')

@section('title', 'Verifikasi Universitas')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <!-- Statistics Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-4 lg:gap-6 xl:gap-8">
            <!-- Pending Card -->
            <div
                class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 lg:p-6 xl:p-8 desktop-card-hover transition-all duration-300 group">
                <div class="absolute top-0 right-0 p-3 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-16 h-16 text-amber-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Menunggu Verifikasi</p>
                        <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">
                            {{ number_format($statistics['pending'] ?? 0) }}</p>
                    </div>
                    <div
                        class="w-12 h-12 bg-amber-50 dark:bg-amber-900/20 rounded-xl flex items-center justify-center border border-amber-100 dark:border-amber-800/50">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Approved Card -->
            <div
                class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                <div class="absolute top-0 right-0 p-3 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-16 h-16 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Selesai Diverifikasi</p>
                        <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">
                            {{ number_format($statistics['approved_this_month'] ?? 0) }}</p>
                    </div>
                    <div
                        class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex items-center justify-center border border-emerald-100 dark:border-emerald-800/50">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- SK Card -->
            <div
                class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                <div class="absolute top-0 right-0 p-3 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-16 h-16 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">SK Diterbitkan</p>
                        <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">
                            {{ number_format($statistics['sk_issued'] ?? 0) }}</p>
                    </div>
                    <div
                        class="w-12 h-12 bg-purple-50 dark:bg-purple-900/20 rounded-xl flex items-center justify-center border border-purple-100 dark:border-purple-800/50">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Review Card -->
            <div
                class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                <div class="absolute top-0 right-0 p-3 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-16 h-16 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Waktu Verifikasi (Avg)</p>
                        <p class="text-3xl font-black text-gray-900 dark:text-white mt-2">
                            {{ number_format($statistics['avg_review_time_hours'] ?? 0, 1) }}h</p>
                    </div>
                    <div
                        class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center border border-blue-100 dark:border-blue-800/50">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section with Integrated Filter -->
        <div class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            <div class="px-5 lg:px-6 xl:px-8 py-4 lg:py-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-base lg:text-lg xl:text-xl font-semibold text-gray-900 dark:text-white">Antrean Verifikasi Prestasi Universitas</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Total: <strong class="text-gray-900 dark:text-white">{{ $achievements->total() }}</strong> prestasi menanti
                        </p>
                    </div>
                </div>

                <!-- Integrated Compact Filter -->
                <form method="GET" action="{{ route('admin.university.index') }}" class="space-y-4">
                    <div class="flex flex-wrap lg:flex-nowrap items-center gap-3">
                        <!-- Search Field -->
                        <div class="relative flex-1 w-full lg:min-w-[300px]">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama, NIM, Event..."
                                class="w-full pl-10 pr-4 py-2 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 transition-all outline-none">
                        </div>

                        <!-- Divider for Desktop -->
                        <div class="hidden lg:block w-px h-8 bg-gray-200 dark:bg-gray-700"></div>

                        <!-- Dropdown Filters Group -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full lg:w-auto">
                            <!-- Faculty -->
                            <select name="faculty" onchange="this.form.submit()"
                                class="w-full lg:w-44 px-3 py-2 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 outline-none cursor-pointer appearance-none">
                                <option value="">Semua Fakultas</option>
                                @foreach($faculties ?? [] as $faculty)
                                    <option value="{{ $faculty['id'] }}" {{ (string) request('faculty') === (string) $faculty['id'] ? 'selected' : '' }}>
                                        {{ $faculty['nama'] }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Level -->
                            <select name="level" onchange="this.form.submit()"
                                class="w-full lg:w-36 px-3 py-2 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 outline-none cursor-pointer appearance-none">
                                <option value="">Tingkat</option>
                                <option value="Universitas" {{ request('level') === 'Universitas' ? 'selected' : '' }}>Universitas</option>
                                <option value="Nasional" {{ request('level') === 'Nasional' ? 'selected' : '' }}>Nasional</option>
                                <option value="Internasional" {{ request('level') === 'Internasional' ? 'selected' : '' }}>Internasional</option>
                            </select>

                            <!-- Sort -->
                            <select name="sort_date" onchange="this.form.submit()"
                                class="w-full lg:w-32 px-3 py-2 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 outline-none cursor-pointer appearance-none">
                                <option value="newest" {{ request('sort_date') === 'newest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="oldest" {{ request('sort_date') === 'oldest' ? 'selected' : '' }}>Terlama</option>
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 w-full lg:w-auto">
                            @if(request()->hasAny(['search', 'faculty', 'level', 'sort_date']))
                                <a href="?" class="p-2 bg-gray-100 dark:bg-gray-700 text-gray-500 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-all" title="Reset Filters">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </a>
                            @endif
                            <button type="submit" class="flex-1 lg:flex-none px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-bold transition-all shadow-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <span class="hidden sm:inline">Cari</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden p-4 space-y-4">
                @forelse($achievements as $achievement)
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                        <!-- Student -->
                        <div class="flex items-center gap-3 mb-3">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                {{ substr($achievement->student?->name ?? 'N', 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                    {{ $achievement->student?->name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $achievement->student_id }}
                                </p>
                            </div>
                        </div>

                        <!-- Event -->
                        <div class="mb-3">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $achievement->event_name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $achievement->organizer }}
                            </p>
                        </div>

                        <!-- Info Row -->
                        <div
                            class="flex flex-wrap justify-between items-center text-xs text-gray-500 dark:text-gray-400 mb-3 gap-2">
                            <span
                                class="px-2 py-0.5 rounded-md bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                                {{ $achievement->level }}
                            </span>
                            <span>{{ $achievement->faculty_validated_at?->format('d M Y') }}</span>
                        </div>

                        <!-- Action -->
                        <a href="{{ route('admin.university.show', $achievement) }}"
                            class="block w-full text-center py-2.5 bg-purple-600 text-white rounded-lg text-xs font-semibold uppercase tracking-wider hover:bg-purple-700 transition-colors">
                            Verifikasi
                        </a>
                    </div>
                @empty
                    <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-6">
                        Tidak ada prestasi yang perlu divalidasi
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table View -->
            <div class="hidden lg:block overflow-x-auto custom-scrollbar">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th
                                class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Mahasiswa</th>
                            <th
                                class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Prestasi</th>
                            <th
                                class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Penyelenggara</th>
                            <th
                                class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Operator Fakultas</th>
                            <th
                                class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tgl Approve Fakultas</th>
                            <th
                                class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-right text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($achievements as $achievement)
                                            <tr class="desktop-table-row transition-colors">
                                                <td class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5 whitespace-nowrap">
                                                    <div class="flex items-center gap-3 lg:gap-4">
                                                        <div class="relative flex-shrink-0">
                                                            <div
                                                                class="w-11 h-11 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-purple-200 dark:shadow-none">
                                                                {{ substr($achievement->student?->name ?? 'N', 0, 1) }}
                                                            </div>
                                                            <div
                                                                class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white dark:border-gray-800 rounded-full">
                                                            </div>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate max-w-[150px]">
                                                                {{ $achievement->student?->name }}</p>
                                                            <p
                                                                class="text-[11px] text-gray-500 dark:text-gray-400 font-medium tracking-wide uppercase">
                                                                {{ $achievement->student_id }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="max-w-[200px]">
                                                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate"
                                                            title="{{ $achievement->event_name }}">{{ $achievement->event_name }}</p>
                                                        <div class="flex items-center gap-2 mt-1.5">
                                                            @php
                                                                $levelStyles = [
                                                                    'Internasional' => 'bg-purple-50 text-purple-700 border border-purple-100 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800/50',
                                                                    'Nasional' => 'bg-blue-50 text-blue-700 border border-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/50',
                                                                    'Universitas' => 'bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50'
                                                                ];
                                                                $style = $levelStyles[$achievement->level] ?? 'bg-gray-50 text-gray-700 border border-gray-100 dark:bg-gray-900/20 dark:text-gray-400 dark:border-gray-800/50';
                                                            @endphp
                             <span
                                                                class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-md {{ $style }}">
                                                                {{ $achievement->level }}
                                                            </span>
                                                            <span
                                                                class="text-[11px] text-gray-400 dark:text-gray-500 font-medium whitespace-nowrap">
                                                                {{ $achievement->achievement?->category?->name }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-600 dark:text-gray-300 font-medium truncate max-w-[150px]"
                                                        title="{{ $achievement->organizer }}">
                                                        {{ $achievement->organizer }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center gap-2">
                                                        <div
                                                            class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-[10px] font-bold text-gray-500 flex-shrink-0">
                                                            {{ substr($achievement->facultyValidator?->name ?? '?', 0, 1) }}
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="text-sm font-bold text-gray-900 dark:text-white leading-none">
                                                                {{ $achievement->facultyValidator?->name ?? '-' }}</p>
                                                            <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 uppercase">
                                                                {{ $achievement->facultyValidator?->faculty ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-bold text-gray-900 dark:text-white">
                                                        {{ $achievement->faculty_validated_at?->format('d M Y') }}
                                                    </div>
                                                    <div class="text-[11px] text-purple-600 dark:text-purple-400 font-medium mt-0.5">
                                                        {{ $achievement->faculty_validated_at?->diffForHumans() }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                                    <a href="{{ route('admin.university.show', $achievement) }}"
                                                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 text-white rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-md shadow-purple-200 dark:shadow-none hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 group">
                                                        <svg class="w-4 h-4 transform group-hover:rotate-12 transition-transform" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Verifikasi
                                                    </a>
                                                </td>
                                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada prestasi yang perlu
                                        diverifikasi</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Semua prestasi sudah diverifikasi
                                        atau belum ada yang disetujui fakultas</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($achievements->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $achievements->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection