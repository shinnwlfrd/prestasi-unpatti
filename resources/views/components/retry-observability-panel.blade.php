@props([
    'title' => 'Retry Observability',
    'summaryUrl',
    'warningThreshold' => 5,
])

@php($panelId = 'retry-observability-' . \Illuminate\Support\Str::random(8))

<div id="{{ $panelId }}" data-summary-url="{{ $summaryUrl }}" data-warning-threshold="{{ $warningThreshold }}" class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 md:p-8 mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div>
            <p class="text-[11px] font-black text-gray-400 uppercase tracking-[0.25em]">Retry Analytics</p>
            <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $title }}</h3>
        </div>
        <div data-warning-badge class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-wider bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 w-fit">
            Memuat
        </div>
    </div>

    <div data-summary class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6"></div>
    <div data-throughput class="space-y-2"></div>
</div>

<script>
    (() => {
        const root = document.getElementById(@json($panelId));
        if (!root) return;

        const summaryUrl = root.dataset.summaryUrl;
        const warningThreshold = Number(root.dataset.warningThreshold || 5);
        const summaryContainer = root.querySelector('[data-summary]');
        const throughputContainer = root.querySelector('[data-throughput]');
        const warningBadge = root.querySelector('[data-warning-badge]');

        const retryBadgeClass = (warning) => warning
            ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300'
            : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';

        const renderCards = (summary) => {
            const cards = [
                ['Total', summary.total ?? 0, 'text-gray-900 dark:text-white'],
                ['Sent', summary.sent ?? 0, 'text-emerald-600 dark:text-emerald-400'],
                ['Failed', summary.failed ?? 0, 'text-rose-600 dark:text-rose-400'],
                ['Skipped', summary.skipped ?? 0, 'text-amber-600 dark:text-amber-400'],
                ['Max Retry', summary.max_retries_exceeded ?? 0, 'text-rose-700 dark:text-rose-300'],
                ['Retried Today', summary.retried_today ?? 0, 'text-blue-700 dark:text-blue-300'],
            ];

            summaryContainer.innerHTML = cards.map(([label, value, valueClass]) => `
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 px-4 py-4">
                    <p class="text-[11px] font-black uppercase tracking-wider text-gray-400">${label}</p>
                    <p class="mt-2 text-2xl font-black ${valueClass}">${Number(value).toLocaleString('id-ID')}</p>
                </div>
            `).join('');
        };

        const renderThroughput = (throughput = []) => {
            const maxCount = throughput.reduce((max, item) => Math.max(max, Number(item.retry_count || 0)), 1);

            if (!throughput.length) {
                throughputContainer.innerHTML = '<p class="text-sm text-gray-500 dark:text-gray-400">Belum ada throughput retry 7 hari terakhir.</p>';
                return;
            }

            throughputContainer.innerHTML = `
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Throughput Retry 7 Hari</p>
                ${throughput.map((item) => {
                    const count = Number(item.retry_count || 0);
                    const width = Math.max((count / maxCount) * 100, 4);
                    return `
                        <div>
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-300 mb-1">
                                <span>${item.retry_date}</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-100">${count}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-600 dark:bg-blue-500 h-2 rounded-full" style="width: ${width}%"></div>
                            </div>
                        </div>
                    `;
                }).join('')}
            `;
        };

        const render = (payload) => {
            const summary = payload.summary ?? {};
            const warning = (summary.max_retries_exceeded ?? 0) >= warningThreshold || summary.retry_warning === true;
            warningBadge.className = `inline-flex items-center rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-wider w-fit ${retryBadgeClass(warning)}`;
            warningBadge.textContent = warning ? 'Perlu Atensi' : 'Stabil';
            renderCards(summary);
            renderThroughput(payload.retry_throughput ?? []);
        };

        const renderError = () => {
            warningBadge.className = 'inline-flex items-center rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-wider w-fit bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300';
            warningBadge.textContent = 'Gangguan';
            summaryContainer.innerHTML = '<div class="col-span-2 md:col-span-6 rounded-2xl border border-dashed border-rose-200 dark:border-rose-800 px-4 py-5 text-sm text-rose-600 dark:text-rose-300">Gagal memuat data observability retry.</div>';
            throughputContainer.innerHTML = '';
        };

        const load = async () => {
            try {
                const response = await fetch(summaryUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) throw new Error('failed');
                const payload = await response.json();
                render(payload);
            } catch (error) {
                renderError();
            }
        };

        load();
        window.setInterval(load, 30000);
    })();
</script>
