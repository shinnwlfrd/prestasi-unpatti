@extends('layouts.admin')

@section('title', 'Notification Delivery Log')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['total'] ?? 0) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-xs text-gray-500 dark:text-gray-400">Sent</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($summary['sent'] ?? 0) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-xs text-gray-500 dark:text-gray-400">Failed</p>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($summary['failed'] ?? 0) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-xs text-gray-500 dark:text-gray-400">Skipped</p>
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($summary['skipped'] ?? 0) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-xs text-gray-500 dark:text-gray-400">Max Retries Exceeded</p>
                <p class="text-2xl font-bold text-rose-700 dark:text-rose-400">{{ number_format($summary['max_retries_exceeded'] ?? 0) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-xs text-gray-500 dark:text-gray-400">Retried Today</p>
                <p class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ number_format($summary['retried_today'] ?? 0) }}</p>
            </div>
        </div>

        @if(($summary['retry_warning'] ?? false) === true)
            <div class="rounded-xl border border-rose-300 dark:border-rose-700 bg-rose-50 dark:bg-rose-900/20 p-4">
                <p class="text-sm font-semibold text-rose-700 dark:text-rose-300">
                    Peringatan: jumlah `max_retries_exceeded` sudah melewati threshold operasional.
                </p>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Retry Throughput 7 Hari Terakhir</h3>
            @php
                $maxCount = $retryThroughput->max('retry_count') ?: 1;
            @endphp
            <div class="space-y-3">
                @forelse($retryThroughput as $item)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-gray-600 dark:text-gray-300">{{ $item->retry_date }}</span>
                            <span class="text-xs font-semibold text-gray-900 dark:text-white">{{ $item->retry_count }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-blue-600 dark:bg-blue-500 h-2 rounded-full transition-all" style="width: {{ ($item->retry_count / $maxCount) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-500 dark:text-gray-400">Belum ada data retry 7 hari terakhir.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="text-xs text-gray-600 dark:text-gray-300">Status</label>
                    <select name="status" class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900">
                        <option value="">Semua</option>
                        @foreach(['sent' => 'sent', 'failed' => 'failed', 'skipped_user_not_linked' => 'skipped_user_not_linked', 'skipped_student_not_found' => 'skipped_student_not_found'] as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['status'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-600 dark:text-gray-300">Student ID</label>
                    <input name="student_id" value="{{ $filters['student_id'] ?? '' }}" class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900" placeholder="2026xxxx" />
                </div>
                <div>
                    <label class="text-xs text-gray-600 dark:text-gray-300">Type</label>
                    <input name="notification_type" value="{{ $filters['notification_type'] ?? '' }}" class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900" placeholder="App\\Notifications\\..." />
                </div>
                <div>
                    <label class="text-xs text-gray-600 dark:text-gray-300">Date From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900" />
                </div>
                <div>
                    <label class="text-xs text-gray-600 dark:text-gray-300">Date To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900" />
                </div>

                <div class="md:col-span-5 flex gap-2 justify-end">
                    <a href="{{ route('admin.notification-delivery-logs.index') }}" class="px-3 py-2 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700">Reset</a>
                    <button type="submit" class="px-4 py-2 text-xs rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-semibold">Filter</button>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Waktu</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Status</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Student</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">SA ID</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Action</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Type</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Error</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                                <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-200 whitespace-nowrap">
                                    {{ optional($log->created_at)->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-4 py-3 text-xs whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-full text-white {{ str_starts_with($log->status, 'skipped') ? 'bg-amber-600' : ($log->status === 'failed' ? 'bg-red-600' : 'bg-emerald-600') }}">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-200 whitespace-nowrap">{{ $log->student_id ?? '-' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-200 whitespace-nowrap">{{ $log->sa_id ?? '-' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-200 whitespace-nowrap">{{ $log->action ?? '-' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-200">{{ class_basename($log->notification_type) }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-200 max-w-[260px] truncate" title="{{ $log->error_message }}">
                                    {{ $log->error_message ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-200 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-full text-white {{ $log->retry_count > 0 ? 'bg-blue-600' : 'bg-gray-400' }}">
                                        {{ $log->retry_count }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada log.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $logs->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
