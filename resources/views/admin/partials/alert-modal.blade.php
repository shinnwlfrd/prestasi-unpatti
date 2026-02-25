{{--
Context-Aware Alert System
─────────────────────────────
Bell Notification Icon → inserted in navbar
Toast Pop-up → auto-shown on dashboard load
Modal Pop-up → opened when bell is clicked
Tab System inside modal → SLA Breach | Duplicates | Missing Docs | Drafts
Context switching → active / archive / global
--}}

@php
    $anomalies = $anomalies ?? ['sla_breach' => 0, 'no_docs' => 0, 'duplicates' => 0, 'abandoned_drafts' => 0];
    $alertContext = $alertContext ?? 'active';
    $globalBreakdown = $globalBreakdown ?? [];
    $totalAnomalies = array_sum($anomalies);

    // Context-dependent color scheme
    $contextColors = match ($alertContext) {
        'active' => [
            'badge' => 'bg-red-500',
            'ring' => 'ring-red-400/50',
            'accent' => 'text-red-500 dark:text-red-400',
            'bgAccent' => 'bg-red-50 dark:bg-red-900/20',
            'border' => 'border-red-200 dark:border-red-800',
            'tabActive' => 'bg-red-500 text-white',
            'tabIdle' => 'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20',
            'label' => 'Periode Aktif',
            'labelColor' => 'text-red-600 dark:text-red-400',
            'icon' => '🚨',
            'gradient' => 'from-red-500/10 to-orange-500/10',
        ],
        'archive' => [
            'badge' => 'bg-blue-500',
            'ring' => 'ring-blue-400/50',
            'accent' => 'text-blue-500 dark:text-blue-400',
            'bgAccent' => 'bg-blue-50 dark:bg-blue-900/20',
            'border' => 'border-blue-200 dark:border-blue-800',
            'tabActive' => 'bg-blue-500 text-white',
            'tabIdle' => 'text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20',
            'label' => 'Periode Arsip',
            'labelColor' => 'text-blue-600 dark:text-blue-400',
            'icon' => 'ℹ️',
            'gradient' => 'from-blue-500/10 to-slate-500/10',
        ],
        'global' => [
            'badge' => 'bg-purple-500',
            'ring' => 'ring-purple-400/50',
            'accent' => 'text-purple-500 dark:text-purple-400',
            'bgAccent' => 'bg-purple-50 dark:bg-purple-900/20',
            'border' => 'border-purple-200 dark:border-purple-800',
            'tabActive' => 'bg-purple-500 text-white',
            'tabIdle' => 'text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20',
            'label' => 'Semua Periode',
            'labelColor' => 'text-purple-600 dark:text-purple-400',
            'icon' => '📊',
            'gradient' => 'from-purple-500/10 to-indigo-500/10',
        ],
    };

    // Context-dependent labels
    $draftLabel = $alertContext === 'archive' ? 'Tingkat Drop-out Pengajuan' : 'Draft Terbengkalai';
    $slaLabel = $alertContext === 'archive' ? 'Keterlambatan Verifikasi (Historis)' : 'SLA Breach';
@endphp

{{-- ═══════════════════════════════════════════════════
TOAST NOTIFICATION (Auto-appears for 5 seconds)
═══════════════════════════════════════════════════ --}}
@if($totalAnomalies > 0 && $alertContext === 'active')
    <div id="anomalyToast"
        class="fixed top-6 right-6 z-[100] max-w-sm w-full transform translate-x-[120%] transition-transform duration-500 ease-out"
        role="alert">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border {{ $contextColors['border'] }} overflow-hidden">
            {{-- Animated top accent line --}}
            <div class="h-1 bg-gradient-to-r from-red-500 via-orange-500 to-yellow-500 animate-pulse"></div>
            <div class="p-4 flex items-start gap-3">
                <div
                    class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white">Peringatan Sistem</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 leading-relaxed">
                        🚨 Terdapat
                        @if($anomalies['sla_breach'] > 0)
                            <span class="font-bold text-red-600 dark:text-red-400">{{ $anomalies['sla_breach'] }} SLA
                                Breach</span>
                        @endif
                        @if($anomalies['duplicates'] > 0)
                            @if($anomalies['sla_breach'] > 0) dan @endif
                            <span class="font-bold text-orange-600 dark:text-orange-400">{{ $anomalies['duplicates'] }}
                                Duplikasi</span>
                        @endif
                        @if($anomalies['no_docs'] > 0)
                            @if($anomalies['sla_breach'] > 0 || $anomalies['duplicates'] > 0), @endif
                            <span class="font-bold text-amber-600 dark:text-amber-400">{{ $anomalies['no_docs'] }} Dok.
                                Kosong</span>
                        @endif
                        @if($anomalies['abandoned_drafts'] > 0)
                            @if($anomalies['sla_breach'] > 0 || $anomalies['duplicates'] > 0 || $anomalies['no_docs'] > 0),
                            @endif
                            <span class="font-bold text-gray-600 dark:text-gray-400">{{ $anomalies['abandoned_drafts'] }}
                                Draft</span>
                        @endif
                        . Klik ikon lonceng untuk detail.
                    </p>
                </div>
                <button onclick="dismissToast()"
                    class="flex-shrink-0 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            {{-- Progress bar --}}
            <div class="h-0.5 bg-gray-100 dark:bg-gray-700">
                <div id="toastProgress" class="h-full bg-red-500 transition-all duration-100 ease-linear"
                    style="width: 100%"></div>
            </div>
        </div>
    </div>
@endif

{{-- ═══════════════════════════════════════════════════
ANOMALY MODAL (Full-screen overlay)
═══════════════════════════════════════════════════ --}}
<div id="anomalyModal" class="fixed inset-0 z-[90] hidden" role="dialog" aria-modal="true"
    aria-labelledby="anomalyModalTitle">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAnomalyModal()"></div>

    {{-- Modal Panel --}}
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="relative w-full max-w-3xl max-h-[85vh] bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden flex flex-col transform scale-95 opacity-0 transition-all duration-300"
            id="anomalyModalPanel">

            {{-- Header --}}
            <div
                class="flex-shrink-0 px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r {{ $contextColors['gradient'] }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-11 h-11 rounded-xl {{ $contextColors['bgAccent'] }} flex items-center justify-center">
                            <svg class="w-6 h-6 {{ $contextColors['accent'] }}" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <div>
                            <h2 id="anomalyModalTitle" class="text-lg font-black text-gray-900 dark:text-white">
                                Pusat Peringatan Sistem
                            </h2>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span
                                    class="text-[10px] font-black uppercase tracking-[0.15em] {{ $contextColors['labelColor'] }}">
                                    {{ $contextColors['icon'] }} {{ $contextColors['label'] }}
                                </span>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">•</span>
                                <span class="text-[10px] font-semibold text-gray-400 dark:text-gray-500">
                                    {{ $totalAnomalies }} masalah terdeteksi
                                </span>
                            </div>
                        </div>
                    </div>
                    <button onclick="closeAnomalyModal()"
                        class="p-2 rounded-xl hover:bg-gray-200/60 dark:hover:bg-gray-600/60 transition-colors">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Tab Navigation --}}
            <div class="flex-shrink-0 px-6 pt-4 pb-0">
                <div class="flex gap-2 overflow-x-auto pb-2 custom-scrollbar" id="anomalyTabs">
                    <button onclick="switchAnomalyTab('sla_breach')" data-tab="sla_breach"
                        class="anomaly-tab-btn flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-200 {{ $contextColors['tabActive'] }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $slaLabel }}
                        @if($anomalies['sla_breach'] > 0)
                            <span
                                class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold bg-white/30 dark:bg-black/20">{{ $anomalies['sla_breach'] }}</span>
                        @endif
                    </button>

                    <button onclick="switchAnomalyTab('duplicates')" data-tab="duplicates"
                        class="anomaly-tab-btn flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-200 {{ $contextColors['tabIdle'] }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Duplikasi Data
                        @if($anomalies['duplicates'] > 0)
                            <span
                                class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300">{{ $anomalies['duplicates'] }}</span>
                        @endif
                    </button>

                    <button onclick="switchAnomalyTab('no_docs')" data-tab="no_docs"
                        class="anomaly-tab-btn flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-200 {{ $contextColors['tabIdle'] }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Dokumen Kosong
                        @if($anomalies['no_docs'] > 0)
                            <span
                                class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">{{ $anomalies['no_docs'] }}</span>
                        @endif
                    </button>

                    <button onclick="switchAnomalyTab('abandoned_drafts')" data-tab="abandoned_drafts"
                        class="anomaly-tab-btn flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-200 {{ $contextColors['tabIdle'] }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        {{ $draftLabel }}
                        @if($anomalies['abandoned_drafts'] > 0)
                            <span
                                class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300">{{ $anomalies['abandoned_drafts'] }}</span>
                        @endif
                    </button>
                </div>
            </div>

            {{-- Tab Content Area --}}
            <div class="flex-1 overflow-y-auto px-6 py-4 custom-scrollbar" id="anomalyTabContent">
                {{-- Loading spinner --}}
                <div id="anomalyLoading" class="hidden flex flex-col items-center justify-center py-16">
                    <div
                        class="w-8 h-8 border-3 border-gray-200 dark:border-gray-600 border-t-purple-500 rounded-full animate-spin">
                    </div>
                    <p class="mt-3 text-xs font-medium text-gray-400">Memuat data anomali...</p>
                </div>

                {{-- Data container --}}
                <div id="anomalyDataContainer"></div>

                {{-- Empty state --}}
                <div id="anomalyEmpty" class="hidden flex flex-col items-center justify-center py-16">
                    <div
                        class="w-16 h-16 rounded-2xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-900 dark:text-white">Tidak Ada Masalah</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Semua data dalam kondisi baik.</p>
                </div>
            </div>

            {{-- Footer --}}
            @if($alertContext === 'global' && !empty($globalBreakdown))
                <div
                    class="flex-shrink-0 px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.15em] mb-3">Breakdown per Periode
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($globalBreakdown as $bp)
                            <div
                                class="px-3 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs">
                                <span class="font-bold text-gray-900 dark:text-white">{{ $bp['period_name'] }}</span>
                                <span class="text-gray-400 mx-1">—</span>
                                <span
                                    class="{{ $bp['is_active'] ? 'text-red-500' : 'text-blue-500' }} font-bold">{{ $bp['total'] }}
                                    masalah</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════
JAVASCRIPT ENGINE
═══════════════════════════════════════════════════ --}}
@push('scripts')
    <script>
        (function () {
            'use strict';

            // Configuration from Blade
            const ALERT_CONTEXT = @json($alertContext);
            const ANOMALIES = @json($anomalies);
            const TOTAL = {{ $totalAnomalies }};
            const PERIOD_ID = @json(request()->input('period') ?? '');
            const API_BASE = @json(rtrim(url('/admin/api/anomalies'), '/'));
            const VERIFY_URL = @json(route('admin.university.index'));

            // Context color scheme for JS-rendered content
            const CTX_SCHEME = @json($contextColors);

            // ─── TOAST ────────────────────────────────────
            const toast = document.getElementById('anomalyToast');
            const progressBar = document.getElementById('toastProgress');
            let toastTimer = null;
            let toastStartTime = null;
            const TOAST_DURATION = 5000;

            function showToastNotification() {
                if (!toast || TOTAL <= 0 || ALERT_CONTEXT !== 'active') return;

                setTimeout(() => {
                    toast.classList.remove('translate-x-[120%]');
                    toast.classList.add('translate-x-0');
                    toastStartTime = Date.now();
                    animateProgress();
                    toastTimer = setTimeout(dismissToast, TOAST_DURATION);
                }, 800);
            }

            function animateProgress() {
                if (!progressBar) return;
                const elapsed = Date.now() - toastStartTime;
                const remaining = Math.max(0, 1 - elapsed / TOAST_DURATION);
                progressBar.style.width = (remaining * 100) + '%';
                if (remaining > 0) {
                    requestAnimationFrame(animateProgress);
                }
            }

            window.dismissToast = function () {
                if (!toast) return;
                clearTimeout(toastTimer);
                toast.classList.remove('translate-x-0');
                toast.classList.add('translate-x-[120%]');
            };

            // ─── MODAL ────────────────────────────────────
            const modal = document.getElementById('anomalyModal');
            const panel = document.getElementById('anomalyModalPanel');
            let currentTab = null;
            let tabDataCache = {};

            window.openAnomalyModal = function () {
                if (!modal) return;
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                requestAnimationFrame(() => {
                    panel.classList.remove('scale-95', 'opacity-0');
                    panel.classList.add('scale-100', 'opacity-100');
                });

                // Auto-select first tab with issues
                const firstTab = ANOMALIES.sla_breach > 0 ? 'sla_breach'
                    : ANOMALIES.duplicates > 0 ? 'duplicates'
                        : ANOMALIES.no_docs > 0 ? 'no_docs'
                            : ANOMALIES.abandoned_drafts > 0 ? 'abandoned_drafts'
                                : 'sla_breach';
                switchAnomalyTab(firstTab);
            };

            window.closeAnomalyModal = function () {
                if (!modal) return;
                panel.classList.remove('scale-100', 'opacity-100');
                panel.classList.add('scale-95', 'opacity-0');

                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }, 300);
            };

            // Close on Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                    closeAnomalyModal();
                }
            });

            // ─── TAB SWITCHING ────────────────────────────
            window.switchAnomalyTab = function (tab) {
                currentTab = tab;

                // Update tab button styles
                document.querySelectorAll('.anomaly-tab-btn').forEach(btn => {
                    const isActive = btn.dataset.tab === tab;
                    // Remove all possible active/idle classes first, then add correct ones
                    btn.className = btn.className
                        .replace(/bg-\S+\s?/g, '')
                        .replace(/text-\S+\s?/g, '')
                        .replace(/hover:bg-\S+\s?/g, '')
                        .trim();
                    btn.className = 'anomaly-tab-btn flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-200 ';

                    if (isActive) {
                        btn.className += CTX_SCHEME.tabActive;
                    } else {
                        btn.className += CTX_SCHEME.tabIdle;
                    }
                });

                // Re-apply badge styles after class reset
                document.querySelectorAll('.anomaly-tab-btn span').forEach(span => {
                    // Keep original badge classes
                });

                loadTabData(tab);
            };

            // ─── DATA LOADING ─────────────────────────────
            async function loadTabData(type) {
                const container = document.getElementById('anomalyDataContainer');
                const loading = document.getElementById('anomalyLoading');
                const empty = document.getElementById('anomalyEmpty');

                container.innerHTML = '';
                empty.classList.add('hidden');

                // Check cache
                if (tabDataCache[type]) {
                    renderTabData(type, tabDataCache[type]);
                    return;
                }

                loading.classList.remove('hidden');

                try {
                    let url = API_BASE + '/' + type + '?context=' + ALERT_CONTEXT;
                    if (PERIOD_ID) url += '&period=' + PERIOD_ID;

                    const resp = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!resp.ok) throw new Error('HTTP ' + resp.status);

                    const result = await resp.json();
                    tabDataCache[type] = result;
                    loading.classList.add('hidden');
                    renderTabData(type, result);
                } catch (err) {
                    loading.classList.add('hidden');
                    container.innerHTML = `
                    <div class="flex flex-col items-center py-12">
                        <div class="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">Gagal Memuat</p>
                        <p class="text-xs text-gray-500 mt-1">${err.message}</p>
                        <button onclick="switchAnomalyTab('${type}')" class="mt-3 text-xs font-bold text-purple-600 hover:text-purple-700">Coba Lagi</button>
                    </div>`;
                }
            }

            // ─── RENDER ───────────────────────────────────
            function renderTabData(type, result) {
                const container = document.getElementById('anomalyDataContainer');
                const empty = document.getElementById('anomalyEmpty');
                const data = result.data || result;
                const context = result.context || ALERT_CONTEXT;

                if (!data || (Array.isArray(data) && data.length === 0)) {
                    empty.classList.remove('hidden');
                    return;
                }

                let html = '';

                switch (type) {
                    case 'sla_breach':
                        html = renderSlaBreach(data, context);
                        break;
                    case 'duplicates':
                        html = renderDuplicates(data, context);
                        break;
                    case 'no_docs':
                        html = renderNoDocs(data, context);
                        break;
                    case 'abandoned_drafts':
                        html = renderDrafts(data, context);
                        break;
                }

                container.innerHTML = html;
            }

            function renderSlaBreach(data, context) {
                if (context === 'global') {
                    return renderGlobalAggregate('SLA Breach', data, 'sla_breach');
                }

                let rows = data.map(item => {
                    const urgency = item.business_days > 14 ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'
                        : item.business_days > 10 ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300'
                            : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300';

                    const actionBtn = context === 'active'
                        ? `<a href="${VERIFY_URL}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors shadow-sm">
                         <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                         Proses Sekarang
                       </a>`
                        : context === 'archive'
                            ? `<a href="${VERIFY_URL}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                         <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                         Lihat Detail Data
                       </a>`
                            : '';

                    return `
                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-900/40 hover:bg-gray-100 dark:hover:bg-gray-700/30 transition-colors group">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-[10px] font-black ${urgency}">${item.business_days}d</span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">${escHtml(item.student_name)}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">${escHtml(item.achievement_name)} • ${item.submitted_at}</p>
                        </div>
                    </div>
                    <div class="flex-shrink-0 ml-3">${actionBtn}</div>
                </div>`;
                }).join('');

                return `<div class="space-y-2">${rows}</div>`;
            }

            function renderDuplicates(data, context) {
                if (context === 'global') {
                    return renderGlobalAggregate('Duplikasi Data', data, 'duplicates');
                }

                let groups = data.map(group => {
                    let records = (group.records || []).map(r => `
                    <div class="flex items-center justify-between py-2 px-3 ${r.is_oldest ? 'bg-green-50/50 dark:bg-green-900/10 rounded-lg' : ''}">
                        <div class="flex items-center gap-2 min-w-0">
                            ${r.is_oldest ? '<span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 uppercase">ASLI</span>' : '<span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 uppercase">DUPLIKAT</span>'}
                            <span class="text-xs text-gray-600 dark:text-gray-400">${escHtml(r.nim)}</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">•</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">${r.created_at}</span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">${escHtml(r.validation_status)}</span>
                    </div>`).join('');

                    return `
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 bg-orange-50/50 dark:bg-orange-900/10 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">${escHtml(group.student_name)}</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">${escHtml(group.event_name)} ${group.level ? '• ' + escHtml(group.level) : ''}</p>
                            </div>
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-[10px] font-black bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300">${group.duplicate_count}x</span>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-50 dark:divide-gray-700/50">${records}</div>
                </div>`;
                }).join('');

                return `<div class="space-y-3">${groups}</div>`;
            }

            function renderNoDocs(data, context) {
                if (context === 'global') {
                    return renderGlobalAggregate('Dokumen Kosong', data, 'no_docs');
                }

                const actionBtn = context === 'active'
                    ? `<span class="text-[11px] font-bold text-amber-600 dark:text-amber-400">Perlu Upload</span>`
                    : `<span class="text-[11px] font-bold text-gray-400 dark:text-gray-500">Arsip</span>`;

                let rows = data.map(item => `
                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-900/40 hover:bg-gray-100 dark:hover:bg-gray-700/30 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">${escHtml(item.student_name)}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">${escHtml(item.achievement_name)}${item.period ? ' • ' + escHtml(item.period) : ''}</p>
                        </div>
                    </div>
                    <div class="flex-shrink-0 ml-3">${actionBtn}</div>
                </div>
            `).join('');

                return `<div class="space-y-2">${rows}</div>`;
            }

            function renderDrafts(data, context) {
                if (context === 'global') {
                    const label = 'Draft Terbengkalai';
                    return renderGlobalAggregate(label, data, 'abandoned_drafts');
                }

                let rows = data.map(item => {
                    const daysColor = item.days_abandoned > 60 ? 'text-red-600 dark:text-red-400'
                        : item.days_abandoned > 45 ? 'text-orange-600 dark:text-orange-400'
                            : 'text-yellow-600 dark:text-yellow-400';

                    return `
                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-900/40 hover:bg-gray-100 dark:hover:bg-gray-700/30 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">${escHtml(item.student_name)}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">${escHtml(item.achievement_name)}${item.period ? ' • ' + escHtml(item.period) : ''}</p>
                        </div>
                    </div>
                    <div class="flex-shrink-0 ml-3 text-right">
                        <span class="text-xs font-black ${daysColor}">${item.days_abandoned} hari</span>
                        <p class="text-[10px] text-gray-400 mt-0.5">${item.last_updated || ''}</p>
                    </div>
                </div>`;
                }).join('');

                return `<div class="space-y-2">${rows}</div>`;
            }

            function renderGlobalAggregate(title, data, type) {
                const count = Array.isArray(data) ? data.length : 0;
                const breakdownHtml = @json($globalBreakdown).length > 0
                    ? `<p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Total <span class="font-black text-purple-600 dark:text-purple-400">${count}</span> ${title} ditemukan di seluruh periode.</p>
                   <button onclick="switchAnomalyTab('${type}')" class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold bg-purple-100 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 rounded-xl hover:bg-purple-200 dark:hover:bg-purple-900/40 transition-colors">
                     📊 Lihat Breakdown per Periode
                   </button>`
                    : '';

                // Still render the list
                let listHtml = '';
                if (Array.isArray(data) && data.length > 0) {
                    listHtml = '<div class="mt-4 space-y-2">';
                    data.forEach(item => {
                        listHtml += `
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-900/40">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">${escHtml(item.student_name || '')}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">${escHtml(item.achievement_name || item.event_name || '')}</p>
                        </div>
                        <div class="flex-shrink-0 ml-3">
                            <span class="text-[10px] font-bold px-2 py-1 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                                ${item.business_days ? item.business_days + 'd' : item.days_abandoned ? item.days_abandoned + ' hari' : item.duplicate_count ? item.duplicate_count + 'x' : 'Detail'}
                            </span>
                        </div>
                    </div>`;
                    });
                    listHtml += '</div>';
                }

                return `
            <div class="flex flex-col items-center py-6 text-center">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center mb-4">
                    <span class="text-2xl">📊</span>
                </div>
                <p class="text-lg font-black text-gray-900 dark:text-white">${count} ${title}</p>
                ${breakdownHtml}
            </div>
            ${listHtml}`;
            }

            function escHtml(str) {
                if (!str) return '';
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            // ─── INIT ─────────────────────────────────────
            document.addEventListener('DOMContentLoaded', showToastNotification);
        })();
    </script>
@endpush