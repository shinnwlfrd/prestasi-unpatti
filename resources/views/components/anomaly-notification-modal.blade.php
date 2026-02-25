@props(['anomalies', 'context', 'globalBreakdown' => []])

@php
$totalCount = $anomalies['total_count'] ?? 0;
$contextColors = [
    'active' => ['bg' => 'bg-red-500', 'text' => 'text-red-600', 'border' => 'border-red-500', 'badge' => 'bg-red-100 text-red-800'],
    'archive' => ['bg' => 'bg-blue-500', 'text' => 'text-blue-600', 'border' => 'border-blue-500', 'badge' => 'bg-blue-100 text-blue-800'],
    'global' => ['bg' => 'bg-purple-500', 'text' => 'text-purple-600', 'border' => 'border-purple-500', 'badge' => 'bg-purple-100 text-purple-800'],
];
$colors = $contextColors[$context] ?? $contextColors['active'];

$contextLabels = [
    'active' => 'Periode Aktif - Tindakan Diperlukan',
    'archive' => 'Periode Arsip - Informasi Historis',
    'global' => 'Semua Periode - Analisis Tren',
];
$contextLabel = $contextLabels[$context] ?? 'Peringatan Sistem';
@endphp

<!-- Modal Backdrop -->
<div id="anomalyModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-80" aria-hidden="true" onclick="closeAnomalyModal()"></div>

        <!-- Center modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl dark:bg-gray-800 sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 {{ $colors['bg'] }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold text-white" id="modal-title">
                                Peringatan Sistem
                            </h3>
                            <p class="text-sm text-white text-opacity-90">{{ $contextLabel }}</p>
                        </div>
                    </div>
                    <button onclick="closeAnomalyModal()" class="text-white hover:text-gray-200 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Body with Tabs -->
            <div class="px-6 py-4">
                <!-- Tab Navigation -->
                <div class="flex mb-4 space-x-2 border-b border-gray-200 dark:border-gray-700">
                    <button onclick="switchAnomalyTab('sla')" id="tab-sla" class="px-4 py-2 text-sm font-medium transition-colors border-b-2 anomaly-tab border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                        <span class="flex items-center space-x-2">
                            <span>SLA Breach</span>
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $colors['badge'] }}">{{ $anomalies['sla_breach']['count'] ?? 0 }}</span>
                        </span>
                    </button>
                    <button onclick="switchAnomalyTab('duplicates')" id="tab-duplicates" class="px-4 py-2 text-sm font-medium transition-colors border-b-2 anomaly-tab border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                        <span class="flex items-center space-x-2">
                            <span>Duplikasi Data</span>
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $colors['badge'] }}">{{ $anomalies['duplicates']['count'] ?? 0 }}</span>
                        </span>
                    </button>
                    <button onclick="switchAnomalyTab('docs')" id="tab-docs" class="px-4 py-2 text-sm font-medium transition-colors border-b-2 anomaly-tab border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                        <span class="flex items-center space-x-2">
                            <span>Dokumen Kosong</span>
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $colors['badge'] }}">{{ $anomalies['missing_documents']['count'] ?? 0 }}</span>
                        </span>
                    </button>
                    <button onclick="switchAnomalyTab('drafts')" id="tab-drafts" class="px-4 py-2 text-sm font-medium transition-colors border-b-2 anomaly-tab border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                        <span class="flex items-center space-x-2">
                            <span>{{ $context === 'archive' ? 'Drop-out' : 'Draft Terbengkalai' }}</span>
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $colors['badge'] }}">{{ $anomalies['abandoned_drafts']['count'] ?? 0 }}</span>
                        </span>
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="space-y-4">
                    <!-- SLA Breach Tab -->
                    <div id="content-sla" class="anomaly-content hidden">
                        <div class="p-4 mb-4 rounded-lg {{ $colors['badge'] }}">
                            <h4 class="font-semibold {{ $colors['text'] }}">
                                @if($context === 'active')
                                    ⚠️ Keterlambatan Validasi - Tindakan Segera Diperlukan
                                @elseif($context === 'archive')
                                    📊 Riwayat Keterlambatan Validasi
                                @else
                                    📈 Analisis Keterlambatan Validasi
                                @endif
                            </h4>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                @if($context === 'active')
                                    Data dengan status menunggu lebih dari 7 hari kerja
                                @elseif($context === 'archive')
                                    Data yang memakan waktu validasi lebih dari 7 hari kerja
                                @else
                                    Total keterlambatan validasi di semua periode
                                @endif
                            </p>
                        </div>

                        @if($context === 'global' && count($globalBreakdown) > 0)
                            <!-- Global Breakdown -->
                            <div class="space-y-2">
                                @foreach($globalBreakdown as $period)
                                    @if($period['sla_breach'] > 0)
                                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                                            <div>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $period['period_name'] }}</span>
                                                <span class="ml-2 text-xs {{ $period['is_active'] ? 'text-red-600' : 'text-blue-600' }}">
                                                    {{ $period['is_active'] ? '(Aktif)' : '(Arsip)' }}
                                                </span>
                                            </div>
                                            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $colors['badge'] }}">
                                                {{ $period['sla_breach'] }} kasus
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <!-- Detail List -->
                            <div class="overflow-hidden border border-gray-200 rounded-lg dark:border-gray-700">
                                <div class="overflow-x-auto max-h-96">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Mahasiswa</th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Prestasi</th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Hari Kerja</th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Status</th>
                                                @if($context === 'active')
                                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Aksi</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                            @forelse($anomalies['sla_breach']['items'] ?? [] as $item)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <td class="px-4 py-3 text-sm">
                                                        <div class="font-medium text-gray-900 dark:text-white">{{ $item['student_name'] }}</div>
                                                        <div class="text-gray-500 dark:text-gray-400">{{ $item['student_nim'] }}</div>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $item['achievement_name'] }}</td>
                                                    <td class="px-4 py-3 text-sm">
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $item['working_days_elapsed'] > 10 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                            {{ $item['working_days_elapsed'] }} hari
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $item['status'] }}</td>
                                                    @if($context === 'active')
                                                        <td class="px-4 py-3 text-sm">
                                                            <a href="{{ route('admin.achievements.show', $item['id']) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                                                Proses Sekarang →
                                                            </a>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $context === 'active' ? 5 : 4 }}" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                        Tidak ada data SLA breach
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Duplicates Tab -->
                    <div id="content-duplicates" class="anomaly-content hidden">
                        <div class="p-4 mb-4 rounded-lg {{ $colors['badge'] }}">
                            <h4 class="font-semibold {{ $colors['text'] }}">🔄 Duplikasi Data Prestasi</h4>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Data dengan kombinasi mahasiswa, nama event, dan tingkat lomba yang sama
                            </p>
                        </div>

                        @if($context === 'global' && count($globalBreakdown) > 0)
                            <div class="space-y-2">
                                @foreach($globalBreakdown as $period)
                                    @if($period['duplicates'] > 0)
                                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                                            <div>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $period['period_name'] }}</span>
                                            </div>
                                            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $colors['badge'] }}">
                                                {{ $period['duplicates'] }} kasus
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="space-y-3">
                                @forelse($anomalies['duplicates']['items'] ?? [] as $item)
                                    <div class="p-4 border border-gray-200 rounded-lg dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900 dark:text-white">{{ $item['student_name'] }} ({{ $item['student_nim'] }})</div>
                                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                    Event: <span class="font-medium">{{ $item['event_name'] }}</span>
                                                </div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                                    Tingkat: <span class="font-medium">{{ $item['achievement_level'] }}</span>
                                                </div>
                                            </div>
                                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                                {{ $item['duplicate_count'] }}x duplikat
                                            </span>
                                        </div>
                                        @if($context !== 'archive')
                                            <div class="flex mt-3 space-x-2">
                                                @foreach($item['ids'] as $id)
                                                    <a href="{{ route('admin.achievements.show', $id) }}" class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                                        Lihat #{{ $id }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                                        Tidak ada duplikasi data
                                    </div>
                                @endforelse
                            </div>
                        @endif
                    </div>

                    <!-- Missing Documents Tab -->
                    <div id="content-docs" class="anomaly-content hidden">
                        <div class="p-4 mb-4 rounded-lg {{ $colors['badge'] }}">
                            <h4 class="font-semibold {{ $colors['text'] }}">📄 Dokumen Tidak Lengkap</h4>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Data yang tidak memiliki sertifikat maupun dokumen alternatif
                            </p>
                        </div>

                        @if($context === 'global' && count($globalBreakdown) > 0)
                            <div class="space-y-2">
                                @foreach($globalBreakdown as $period)
                                    @if($period['missing_documents'] > 0)
                                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                                            <div>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $period['period_name'] }}</span>
                                            </div>
                                            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $colors['badge'] }}">
                                                {{ $period['missing_documents'] }} kasus
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="overflow-hidden border border-gray-200 rounded-lg dark:border-gray-700">
                                <div class="overflow-x-auto max-h-96">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Mahasiswa</th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Prestasi</th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Status</th>
                                                @if($context !== 'archive')
                                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Aksi</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                            @forelse($anomalies['missing_documents']['items'] ?? [] as $item)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <td class="px-4 py-3 text-sm">
                                                        <div class="font-medium text-gray-900 dark:text-white">{{ $item['student_name'] }}</div>
                                                        <div class="text-gray-500 dark:text-gray-400">{{ $item['student_nim'] }}</div>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $item['achievement_name'] }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $item['status'] }}</td>
                                                    @if($context !== 'archive')
                                                        <td class="px-4 py-3 text-sm">
                                                            <a href="{{ route('admin.achievements.show', $item['id']) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                                                {{ $context === 'active' ? 'Upload Dokumen →' : 'Lihat Detail →' }}
                                                            </a>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $context !== 'archive' ? 4 : 3 }}" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                        Tidak ada dokumen yang hilang
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Abandoned Drafts Tab -->
                    <div id="content-drafts" class="anomaly-content hidden">
                        <div class="p-4 mb-4 rounded-lg {{ $colors['badge'] }}">
                            <h4 class="font-semibold {{ $colors['text'] }}">
                                @if($context === 'archive')
                                    📉 Tingkat Drop-out Pengajuan
                                @else
                                    ⏰ Draft Terbengkalai
                                @endif
                            </h4>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                @if($context === 'archive')
                                    Draft yang tidak pernah diselesaikan selama periode berlangsung
                                @else
                                    Draft yang tidak diupdate lebih dari 30 hari
                                @endif
                            </p>
                        </div>

                        @if($context === 'global' && count($globalBreakdown) > 0)
                            <div class="space-y-2">
                                @foreach($globalBreakdown as $period)
                                    @if($period['abandoned_drafts'] > 0)
                                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                                            <div>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $period['period_name'] }}</span>
                                            </div>
                                            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $colors['badge'] }}">
                                                {{ $period['abandoned_drafts'] }} draft
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="overflow-hidden border border-gray-200 rounded-lg dark:border-gray-700">
                                <div class="overflow-x-auto max-h-96">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Mahasiswa</th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Prestasi</th>
                                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Hari Terbengkalai</th>
                                                @if($context !== 'archive')
                                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Aksi</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                            @forelse($anomalies['abandoned_drafts']['items'] ?? [] as $item)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <td class="px-4 py-3 text-sm">
                                                        <div class="font-medium text-gray-900 dark:text-white">{{ $item['student_name'] }}</div>
                                                        <div class="text-gray-500 dark:text-gray-400">{{ $item['student_nim'] }}</div>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $item['achievement_name'] }}</td>
                                                    <td class="px-4 py-3 text-sm">
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                            {{ $item['days_abandoned'] }} hari
                                                        </span>
                                                    </td>
                                                    @if($context !== 'archive')
                                                        <td class="px-4 py-3 text-sm">
                                                            <a href="{{ route('admin.achievements.show', $item['id']) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                                                Lihat Detail →
                                                            </a>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $context !== 'archive' ? 4 : 3 }}" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                        Tidak ada draft terbengkalai
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Total: <span class="font-semibold text-gray-900 dark:text-white">{{ $totalCount }}</span> anomali terdeteksi
                    </div>
                    <button onclick="closeAnomalyModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:hover:bg-gray-500">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openAnomalyModal() {
    document.getElementById('anomalyModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    // Open first tab by default
    switchAnomalyTab('sla');
}

function closeAnomalyModal() {
    document.getElementById('anomalyModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function switchAnomalyTab(tabName) {
    // Hide all content
    document.querySelectorAll('.anomaly-content').forEach(el => el.classList.add('hidden'));
    
    // Remove active state from all tabs
    document.querySelectorAll('.anomaly-tab').forEach(el => {
        el.classList.remove('border-blue-500', 'text-blue-600', 'dark:text-blue-400');
        el.classList.add('border-transparent', 'text-gray-600', 'dark:text-gray-400');
    });
    
    // Show selected content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Activate selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
    activeTab.classList.add('border-blue-500', 'text-blue-600', 'dark:text-blue-400');
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAnomalyModal();
    }
});
</script>
