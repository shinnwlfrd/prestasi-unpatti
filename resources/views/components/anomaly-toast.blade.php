@props(['anomalies', 'context'])

@php
$totalCount = $anomalies['total_count'] ?? 0;
$slaCount = $anomalies['sla_breach']['count'] ?? 0;
$duplicateCount = $anomalies['duplicates']['count'] ?? 0;
$missingDocsCount = $anomalies['missing_documents']['count'] ?? 0;
$draftsCount = $anomalies['abandoned_drafts']['count'] ?? 0;

// Only show toast for active period with issues
$shouldShow = $context === 'active' && $totalCount > 0;

$toastColors = [
    'active' => 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900 dark:border-red-700 dark:text-red-200',
    'archive' => 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900 dark:border-blue-700 dark:text-blue-200',
    'global' => 'bg-purple-50 border-purple-200 text-purple-800 dark:bg-purple-900 dark:border-purple-700 dark:text-purple-200',
];
$toastColor = $toastColors[$context] ?? $toastColors['active'];
@endphp

@if($shouldShow)
<div 
    id="anomalyToast" 
    class="fixed z-50 transition-all duration-500 transform translate-x-0 top-20 right-4 w-96"
    style="display: none;"
>
    <div class="overflow-hidden border-2 rounded-lg shadow-lg {{ $toastColor }}">
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1 ml-3">
                    <h3 class="text-sm font-semibold">
                        🚨 Peringatan Sistem Terdeteksi
                    </h3>
                    <div class="mt-2 text-sm">
                        <p class="mb-2">Terdapat {{ $totalCount }} anomali yang memerlukan perhatian:</p>
                        <ul class="space-y-1 text-xs">
                            @if($slaCount > 0)
                                <li>• {{ $slaCount }} SLA Breach (Keterlambatan Validasi)</li>
                            @endif
                            @if($duplicateCount > 0)
                                <li>• {{ $duplicateCount }} Duplikasi Data</li>
                            @endif
                            @if($missingDocsCount > 0)
                                <li>• {{ $missingDocsCount }} Dokumen Tidak Lengkap</li>
                            @endif
                            @if($draftsCount > 0)
                                <li>• {{ $draftsCount }} Draft Terbengkalai</li>
                            @endif
                        </ul>
                    </div>
                    <div class="mt-3">
                        <button 
                            onclick="openAnomalyModal(); closeToast();" 
                            class="text-xs font-medium underline hover:no-underline"
                        >
                            Klik untuk melihat detail →
                        </button>
                    </div>
                </div>
                <div class="flex-shrink-0 ml-4">
                    <button 
                        onclick="closeToast()" 
                        class="inline-flex rounded-md hover:opacity-75 focus:outline-none"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- Progress bar -->
        <div class="h-1 bg-red-200 dark:bg-red-800">
            <div id="toastProgress" class="h-full transition-all duration-100 bg-red-500 dark:bg-red-600" style="width: 100%;"></div>
        </div>
    </div>
</div>

<script>
let toastTimeout;
let progressInterval;

function showToast() {
    const toast = document.getElementById('anomalyToast');
    const progress = document.getElementById('toastProgress');
    
    if (!toast) return;
    
    toast.style.display = 'block';
    
    // Animate progress bar
    let width = 100;
    progressInterval = setInterval(() => {
        width -= 2;
        progress.style.width = width + '%';
        if (width <= 0) {
            clearInterval(progressInterval);
            closeToast();
        }
    }, 100); // 5 seconds total (100 * 50ms)
    
    // Auto close after 5 seconds
    toastTimeout = setTimeout(() => {
        closeToast();
    }, 5000);
}

function closeToast() {
    const toast = document.getElementById('anomalyToast');
    if (toast) {
        toast.style.display = 'none';
    }
    if (toastTimeout) {
        clearTimeout(toastTimeout);
    }
    if (progressInterval) {
        clearInterval(progressInterval);
    }
}

// Show toast on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(showToast, 500); // Small delay for better UX
});
</script>
@endif
