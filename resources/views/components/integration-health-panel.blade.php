@props([
    'title' => 'Status Integrasi',
    'healthUrl',
])

@php($panelId = 'integration-health-' . \Illuminate\Support\Str::random(8))

<div id="{{ $panelId }}" data-health-url="{{ $healthUrl }}" class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 md:p-8 mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div>
            <p class="text-[11px] font-black text-gray-400 uppercase tracking-[0.25em]">Integrasi Eksternal</p>
            <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $title }}</h3>
        </div>
        <div data-overall-badge class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-wider bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 w-fit">
            Memuat
        </div>
    </div>

    <div data-summary class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
        <div class="rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 px-4 py-4 text-sm text-gray-500 dark:text-gray-400 md:col-span-4">
            Memuat ringkasan alert...
        </div>
    </div>

    <div data-services class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 px-4 py-5 text-sm text-gray-500 dark:text-gray-400 lg:col-span-2">
            Memuat status integrasi...
        </div>
    </div>
</div>

<script>
    (() => {
        const root = document.getElementById(@json($panelId));
        if (!root) return;

        const servicesContainer = root.querySelector('[data-services]');
        const summaryContainer = root.querySelector('[data-summary]');
        const overallBadge = root.querySelector('[data-overall-badge]');
        const healthUrl = root.dataset.healthUrl;

        const badgeClass = (status) => {
            switch (status) {
                case 'up':
                    return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
                case 'degraded':
                    return 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
                default:
                    return 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300';
            }
        };

        const statusLabel = (status) => {
            switch (status) {
                case 'up': return 'Sehat';
                case 'degraded': return 'Terdegradasi';
                default: return 'Gangguan';
            }
        };

        const attentionLabel = (level) => {
            switch (level) {
                case 'critical': return 'Kritis';
                case 'warning': return 'Perlu Cek';
                default: return 'Normal';
            }
        };

        const renderSummary = (summary = {}) => {
            const affectedServices = summary.affected_services ?? [];
            summaryContainer.innerHTML = `
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 px-4 py-4">
                    <p class="text-[11px] font-black uppercase tracking-wider text-gray-400">Total Service</p>
                    <p class="mt-2 text-2xl font-black text-gray-900 dark:text-white">${summary.total_services ?? 0}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200 dark:border-emerald-900/50 px-4 py-4">
                    <p class="text-[11px] font-black uppercase tracking-wider text-emerald-500">Sehat</p>
                    <p class="mt-2 text-2xl font-black text-emerald-700 dark:text-emerald-300">${summary.up_services ?? 0}</p>
                </div>
                <div class="rounded-2xl border border-rose-200 dark:border-rose-900/50 px-4 py-4">
                    <p class="text-[11px] font-black uppercase tracking-wider text-rose-500">Down</p>
                    <p class="mt-2 text-2xl font-black text-rose-700 dark:text-rose-300">${summary.down_services ?? 0}</p>
                </div>
                <div class="rounded-2xl border border-amber-200 dark:border-amber-900/50 px-4 py-4">
                    <p class="text-[11px] font-black uppercase tracking-wider text-amber-500">Butuh Perhatian</p>
                    <p class="mt-2 text-2xl font-black text-amber-700 dark:text-amber-300">${summary.services_requiring_attention ?? 0}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 px-4 py-4 md:col-span-4">
                    <p class="text-[11px] font-black uppercase tracking-wider text-gray-400 mb-3">Ringkasan Alert</p>
                    ${affectedServices.length
                        ? affectedServices.map((item) => `
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 py-2 border-t border-gray-100 dark:border-gray-700 first:border-t-0 first:pt-0">
                                <div>
                                    <p class="text-sm font-black text-gray-900 dark:text-white">${item.service}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        ${attentionLabel(item.attention_level)} • streak gagal ${item.consecutive_failures}
                                    </p>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    gagal terakhir: ${item.last_failure_at ?? '-'}<br>
                                    pulih terakhir: ${item.last_recovered_at ?? '-'}
                                </div>
                            </div>
                        `).join('')
                        : '<p class="text-sm text-gray-500 dark:text-gray-400">Belum ada service yang memerlukan perhatian khusus.</p>'}
                </div>
            `;
        };

        const render = (payload) => {
            const overall = payload.overall_status ?? 'down';
            overallBadge.className = `inline-flex items-center rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-wider w-fit ${badgeClass(overall)}`;
            overallBadge.textContent = statusLabel(overall);
            renderSummary(payload.alert_summary ?? {});

            const history = payload.recent_history ?? {};
            const services = payload.services ?? [];
            servicesContainer.innerHTML = services.map((service) => `
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 px-4 py-4">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <p class="text-sm font-black text-gray-900 dark:text-white">${service.service}</p>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-wider ${badgeClass(service.status)}">
                            ${statusLabel(service.status)}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">${service.message}</p>
                    <p class="text-xs text-gray-400 break-all">${service.base_url ?? '-'}</p>
                    <div class="mt-3 space-y-1">
                        ${(history[service.service] ?? []).map((item) => `
                            <p class="text-[11px] text-gray-400">
                                ${item.checked_at ?? '-'} • ${item.status} • ${item.message ?? '-'}
                            </p>
                        `).join('')}
                    </div>
                </div>
            `).join('');
        };

        const renderError = (message) => {
            overallBadge.className = `inline-flex items-center rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-wider w-fit ${badgeClass('down')}`;
            overallBadge.textContent = 'Gangguan';
            summaryContainer.innerHTML = `
                <div class="rounded-2xl border border-dashed border-rose-200 dark:border-rose-800 px-4 py-5 text-sm text-rose-600 dark:text-rose-300 md:col-span-4">
                    ${message}
                </div>
            `;
            servicesContainer.innerHTML = `
                <div class="rounded-2xl border border-dashed border-rose-200 dark:border-rose-800 px-4 py-5 text-sm text-rose-600 dark:text-rose-300 lg:col-span-2">
                    ${message}
                </div>
            `;
        };

        const load = async () => {
            try {
                const response = await fetch(healthUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) throw new Error('Gagal memuat status integrasi.');

                const payload = await response.json();
                render(payload);
            } catch (error) {
                renderError(error.message);
            }
        };

        load();
        window.setInterval(load, 30000);
    })();
</script>
