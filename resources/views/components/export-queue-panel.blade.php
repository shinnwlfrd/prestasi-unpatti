@props([
    'title' => 'Status Export',
    'recentUrl',
])

@php($panelId = 'export-queue-' . \Illuminate\Support\Str::random(8))

<div id="{{ $panelId }}" data-recent-url="{{ $recentUrl }}" class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 md:p-8 mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div>
            <p class="text-[11px] font-black text-gray-400 uppercase tracking-[0.25em]">Background Export</p>
            <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $title }}</h3>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            File baru akan muncul otomatis saat proses selesai.
        </p>
    </div>

    <div data-export-list class="space-y-3">
        <div class="rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 px-4 py-5 text-sm text-gray-500 dark:text-gray-400">
            Memuat status export...
        </div>
    </div>
</div>

<script>
    (() => {
        const root = document.getElementById(@json($panelId));
        if (!root) {
            return;
        }

        const list = root.querySelector('[data-export-list]');
        const recentUrl = root.dataset.recentUrl;

        const badgeClass = (status) => {
            switch (status) {
                case 'completed':
                    return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
                case 'failed':
                    return 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300';
                case 'processing':
                    return 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
                default:
                    return 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200';
            }
        };

        const renderEmpty = (message) => {
            list.innerHTML = `
                <div class="rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 px-4 py-5 text-sm text-gray-500 dark:text-gray-400">
                    ${message}
                </div>
            `;
        };

        const renderItems = (items) => {
            if (!items.length) {
                renderEmpty('Belum ada export terbaru untuk konteks ini.');
                return;
            }

            list.innerHTML = items.map((item) => `
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 px-4 py-4">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-wider ${badgeClass(item.status)}">
                                    ${item.status_label}
                                </span>
                                <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">${item.format}</span>
                            </div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">${item.scope_name}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                ${item.created_at ?? '-'}${item.row_count ? ` • ${item.row_count} baris` : ''}
                            </p>
                            ${item.error_message ? `<p class="text-xs text-rose-600 dark:text-rose-300">${item.error_message}</p>` : ''}
                        </div>
                        <div class="flex items-center gap-3">
                            ${item.download_url
                                ? `<a href="${item.download_url}" class="inline-flex items-center justify-center rounded-2xl bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-4 py-2 text-sm font-black">Unduh</a>`
                                : `<span class="inline-flex items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 px-4 py-2 text-sm font-black">Menunggu</span>`}
                        </div>
                    </div>
                </div>
            `).join('');
        };

        const load = async () => {
            try {
                const response = await fetch(recentUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Gagal memuat status export.');
                }

                const payload = await response.json();
                renderItems(payload.data ?? []);
            } catch (error) {
                renderEmpty(error.message);
            }
        };

        load();
        window.setInterval(load, 10000);
    })();
</script>
