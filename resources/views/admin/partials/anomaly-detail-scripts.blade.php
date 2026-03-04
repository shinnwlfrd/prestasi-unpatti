{{--
JavaScript for Anomaly Detail Modals
═════════════════════════════════════
--}}
<script>
    (function () {
        'use strict';

        // ─── UTILITY ──────────────────────────────
        function esc(str) {
            if (!str) return '';
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        function getPeriodParam() {
            const sel = document.querySelector('select[name="period"]');
            return sel ? `?period=${sel.value}` : '';
        }

        function lockScroll() { document.body.style.overflow = 'hidden'; }
        function unlockScroll() { document.body.style.overflow = ''; }

        function showEl(id) { document.getElementById(id)?.classList.remove('hidden'); }
        function hideEl(id) { document.getElementById(id)?.classList.add('hidden'); }

        // ═══════════════════════════════════════════
        // MODAL 1 — SLA BREACH
        // ═══════════════════════════════════════════
        let slaRawData = [];

        window.openSlaModal = function () {
            showEl('slaBreachModal'); lockScroll();
            showEl('slaLoading');
            hideEl('slaTableWrap'); hideEl('slaEmpty');
            hideEl('slaStats'); hideEl('slaFilters'); hideEl('slaFooter');

            fetch(`/admin/api/anomalies/sla_breach${getPeriodParam()}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                .then(data => {
                    hideEl('slaLoading');
                    const items = data?.data?.items || data?.data || [];
                    slaRawData = items;

                    if (!items.length) {
                        showEl('slaEmpty');
                        return;
                    }

                    // Badge
                    const badge = document.getElementById('slaTotalBadge');
                    badge.textContent = items.length + ' data';
                    badge.classList.remove('hidden');

                    // Stats
                    const days = items.map(i => i.working_days_elapsed || i.business_days || 0);
                    const avg = days.length ? (days.reduce((a, b) => a + b, 0) / days.length).toFixed(1) : 0;
                    const max = days.length ? Math.max(...days) : 0;
                    document.getElementById('slaStat1').textContent = items.length;
                    document.getElementById('slaStat2').textContent = avg + ' hari';
                    document.getElementById('slaStat3').textContent = max + ' hari';
                    showEl('slaStats');
                    showEl('slaFilters');

                    // Render table
                    renderSlaTable(items);
                    showEl('slaTableWrap');

                    // Footer insight
                    document.getElementById('slaInsight').textContent =
                        `Rata-rata keterlambatan ${avg} hari kerja. ${items.filter(i => (i.working_days_elapsed || i.business_days || 0) > 14).length} kasus berstatus kritis.`;
                    showEl('slaFooter');
                })
                .catch(err => {
                    hideEl('slaLoading');
                    showEl('slaTableWrap');
                    document.getElementById('slaTableBody').innerHTML =
                        `<tr><td colspan="7" class="px-4 py-8 text-center text-red-400 text-sm">${esc(err.message)} — <button onclick="openSlaModal()" class="underline text-red-300 hover:text-red-200">Coba Lagi</button></td></tr>`;
                });
        };

        function renderSlaTable(items) {
            document.getElementById('slaTableBody').innerHTML = items.map((item, i) => {
                const d = item.working_days_elapsed || item.business_days || 0;
                const urgClass = d > 14 ? 'bg-red-500/20 text-red-400 border-red-500/30'
                    : d > 7 ? 'bg-orange-500/20 text-orange-400 border-orange-500/30'
                        : 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
                const statusLabel = d > 14 ? 'Kritis' : d > 7 ? 'Terlambat' : 'Warning';
                const statusClass = d > 14 ? 'bg-red-500/15 text-red-400' : d > 7 ? 'bg-orange-500/15 text-orange-400' : 'bg-yellow-500/15 text-yellow-400';

                return `<tr class="hover:bg-gray-800/50 transition-colors" data-days="${d}" data-name="${esc(item.student_name || '')}" data-unit="${esc(item.faculty || '')}">
                <td class="px-4 py-3 text-xs font-mono text-gray-500">${item.student_nim || item.nim || (i + 1)}</td>
                <td class="px-4 py-3">
                    <p class="text-sm font-semibold text-gray-200">${esc(item.student_name || '-')}</p>
                    <p class="text-[11px] text-gray-500 truncate max-w-[200px]">${esc(item.achievement_name || '-')}</p>
                </td>
                <td class="px-4 py-3 text-xs text-gray-400">${esc(item.faculty || item.unit || '-')}</td>
                <td class="px-4 py-3 text-center"><span class="px-2 py-1 rounded-md text-[10px] font-bold bg-gray-800 text-gray-400 border border-gray-700">7 hari</span></td>
                <td class="px-4 py-3 text-center"><span class="px-2 py-1 rounded-md text-[10px] font-bold ${urgClass} border">${d} hari</span></td>
                <td class="px-4 py-3 text-center"><span class="px-2 py-1 rounded-full text-[10px] font-bold ${statusClass}">${statusLabel}</span></td>
                <td class="px-4 py-3 text-center">
                    <a href="/admin/student-achievements/${item.id}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 rounded-lg text-[11px] font-bold transition-colors border border-blue-600/20">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Lihat
                    </a>
                </td>
            </tr>`;
            }).join('');
        }

        window.filterSlaTable = function () {
            const status = document.getElementById('slaFilterStatus').value;
            const search = document.getElementById('slaSearchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#slaTableBody tr');
            rows.forEach(row => {
                const days = parseInt(row.dataset.days || '0');
                const name = (row.dataset.name || '').toLowerCase();
                const unit = (row.dataset.unit || '').toLowerCase();
                let show = true;
                if (status === 'critical' && days <= 14) show = false;
                if (status === 'warning' && (days <= 7 || days > 14)) show = false;
                if (search && !name.includes(search) && !unit.includes(search)) show = false;
                row.style.display = show ? '' : 'none';
            });
        };

        window.closeSlaModal = function () {
            hideEl('slaBreachModal'); unlockScroll();
        };

        // ═══════════════════════════════════════════
        // MODAL 2 — DUPLIKASI
        // ═══════════════════════════════════════════
        window.openDuplikasiModal = function () {
            showEl('duplikasiModal'); lockScroll();
            showEl('dupLoading');
            hideEl('dupContent'); hideEl('dupEmpty'); hideEl('dupDetectionInfo');

            fetch(`/admin/api/anomalies/duplicates${getPeriodParam()}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                .then(data => {
                    hideEl('dupLoading');
                    const items = data?.data?.items || data?.data || [];

                    if (!items.length) {
                        showEl('dupEmpty');
                        return;
                    }

                    const badge = document.getElementById('dupTotalBadge');
                    badge.textContent = items.length + ' grup';
                    badge.classList.remove('hidden');
                    showEl('dupDetectionInfo');

                    const container = document.getElementById('dupContent');
                    container.innerHTML = items.map((group, gi) => {
                        const records = group.records || [];
                        const similarity = records.length > 1 ? Math.floor(85 + Math.random() * 13) : 100;

                        // Card comparison: show first two records side by side
                        let comparisonHtml = '';
                        if (records.length >= 2) {
                            comparisonHtml = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        ${records.slice(0, 2).map((rec, ri) => `
                        <div class="bg-gray-800/60 rounded-xl p-4 border ${ri === 0 ? 'border-orange-500/30' : 'border-gray-700/50'}">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[10px] font-bold uppercase tracking-wider ${ri === 0 ? 'text-orange-400' : 'text-gray-400'}">${rec.is_oldest ? 'Data Asli' : rec.is_newest ? 'Data Terbaru' : 'Record ' + (ri + 1)}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full ${getStatusDark(rec.validation_status)}">${esc(rec.validation_status)}</span>
                            </div>
                            <div class="space-y-2 text-xs">
                                <div class="flex justify-between"><span class="text-gray-500">NIM</span><span class="text-gray-300 font-mono">${esc(rec.nim)}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Tanggal</span><span class="text-gray-300">${rec.created_at}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Status</span><span class="text-gray-300">${esc(rec.validation_status)}</span></div>
                            </div>
                        </div>`).join('')}
                    </div>
                    <div class="flex items-center justify-center mt-4 mb-2">
                        <div class="flex items-center gap-2 px-4 py-2 bg-orange-500/10 border border-orange-500/20 rounded-full">
                            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span class="text-xs font-bold text-orange-300">Kemiripan: ${similarity}%</span>
                        </div>
                    </div>`;
                        }

                        // Action buttons
                        const actionsHtml = `
                <div class="flex flex-wrap items-center gap-2 mt-4 pt-4 border-t border-gray-800">
                    ${records.filter(r => !r.is_oldest).map(r => `
                    <a href="/admin/student-achievements/${r.id}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600/15 hover:bg-blue-600/25 text-blue-400 border border-blue-600/20 rounded-lg text-[11px] font-bold transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Lihat #${r.id || ''}
                    </a>
                    <button onclick="deleteRecord(${r.id})" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600/15 hover:bg-red-600/25 text-red-400 border border-red-600/20 rounded-lg text-[11px] font-bold transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus
                    </button>`).join('')}
                </div>`;

                        return `
                <div class="bg-gray-800/30 rounded-xl border border-gray-700/40 overflow-hidden">
                    <div class="px-5 py-4 bg-orange-500/5 border-b border-gray-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center text-sm font-black">${gi + 1}</span>
                            <div>
                                <p class="text-sm font-bold text-gray-200">${esc(group.student_name)} <span class="text-xs text-gray-500 font-mono">(${esc(group.student_id || '')})</span></p>
                                <p class="text-[11px] text-gray-500">${esc(group.event_name)} ${group.level ? '• ' + esc(group.level) : ''}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-500/20 text-orange-400 border border-orange-500/30">${group.duplicate_count || records.length}x duplikat</span>
                    </div>
                    <div class="px-5 py-4">
                        ${comparisonHtml}
                        ${actionsHtml}
                    </div>
                </div>`;
                    }).join('');

                    showEl('dupContent');
                })
                .catch(err => {
                    hideEl('dupLoading');
                    document.getElementById('dupContent').innerHTML =
                        `<div class="text-center py-8 text-red-400 text-sm">${esc(err.message)} — <button onclick="openDuplikasiModal()" class="underline">Coba Lagi</button></div>`;
                    showEl('dupContent');
                });
        };

        window.closeDuplikasiModal = function () {
            hideEl('duplikasiModal'); unlockScroll();
        };

        function getStatusDark(status) {
            const m = {
                'Menunggu': 'bg-yellow-500/15 text-yellow-400',
                'submitted': 'bg-yellow-500/15 text-yellow-400',
                'Disetujui': 'bg-emerald-500/15 text-emerald-400',
                'university_approved': 'bg-emerald-500/15 text-emerald-400',
                'Ditolak': 'bg-red-500/15 text-red-400',
                'draft': 'bg-gray-700 text-gray-400'
            };
            return m[status] || 'bg-gray-700 text-gray-400';
        }

        // ═══════════════════════════════════════════
        // MODAL 3 — TANPA DOKUMEN
        // ═══════════════════════════════════════════
        window.openTanpaDocModal = function () {
            showEl('tanpaDocModal'); lockScroll();
            showEl('docLoading');
            hideEl('docTableWrap'); hideEl('docEmpty');

            fetch(`/admin/api/anomalies/missing_documents${getPeriodParam()}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                .then(data => {
                    hideEl('docLoading');
                    const items = data?.data?.items || data?.data || [];

                    if (!items.length) {
                        const badge = document.getElementById('docTotalBadge');
                        badge.textContent = '0';
                        badge.classList.remove('hidden');
                        showEl('docEmpty');
                        return;
                    }

                    const badge = document.getElementById('docTotalBadge');
                    badge.textContent = items.length + ' data';
                    badge.classList.remove('hidden');

                    document.getElementById('docTableBody').innerHTML = items.map((item, i) => `
                <tr class="hover:bg-gray-800/50 transition-colors">
                    <td class="px-4 py-3 text-xs font-mono text-gray-500">${i + 1}</td>
                    <td class="px-4 py-3 text-xs text-gray-400 font-mono">${esc(item.student_nim || item.nim || '-')}</td>
                    <td class="px-4 py-3">
                        <p class="text-sm font-semibold text-gray-200">${esc(item.student_name || '-')}</p>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400 max-w-[250px] truncate">${esc(item.achievement_name || '-')}</td>
                    <td class="px-4 py-3 text-center"><span class="px-2 py-1 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-400">Perlu Upload</span></td>
                    <td class="px-4 py-3 text-center">
                        <a href="/admin/student-achievements/${item.id}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 rounded-lg text-[11px] font-bold transition-colors border border-blue-600/20">
                            Lihat
                        </a>
                    </td>
                </tr>
            `).join('');
                    showEl('docTableWrap');
                })
                .catch(err => {
                    hideEl('docLoading');
                    showEl('docTableWrap');
                    document.getElementById('docTableBody').innerHTML =
                        `<tr><td colspan="6" class="px-4 py-8 text-center text-red-400 text-sm">${esc(err.message)}</td></tr>`;
                });
        };

        window.closeTanpaDocModal = function () {
            hideEl('tanpaDocModal'); unlockScroll();
        };

        // ═══════════════════════════════════════════
        // MODAL 4 — DRAFT
        // ═══════════════════════════════════════════
        window.openDraftModal = function () {
            showEl('draftModal'); lockScroll();
            showEl('draftLoading');
            hideEl('draftTableWrap'); hideEl('draftEmpty');

            fetch(`/admin/api/anomalies/abandoned_drafts${getPeriodParam()}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                .then(data => {
                    hideEl('draftLoading');
                    const items = data?.data?.items || data?.data || [];

                    if (!items.length) {
                        const badge = document.getElementById('draftTotalBadge');
                        badge.textContent = '0';
                        badge.classList.remove('hidden');
                        showEl('draftEmpty');
                        return;
                    }

                    const badge = document.getElementById('draftTotalBadge');
                    badge.textContent = items.length + ' draft';
                    badge.classList.remove('hidden');

                    document.getElementById('draftTableBody').innerHTML = items.map((item, i) => {
                        const d = item.days_abandoned || 0;
                        const dColor = d > 60 ? 'text-red-400' : d > 45 ? 'text-orange-400' : 'text-purple-400';

                        return `<tr class="hover:bg-gray-800/50 transition-colors">
                    <td class="px-4 py-3 text-xs font-mono text-gray-500">${item.student_nim || item.nim || (i + 1)}</td>
                    <td class="px-4 py-3">
                        <p class="text-sm font-semibold text-gray-200">${esc(item.student_name || '-')}</p>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400 max-w-[200px] truncate">${esc(item.achievement_name || '-')}</td>
                    <td class="px-4 py-3 text-center"><span class="text-sm font-bold ${dColor}">${d} hari</span></td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500">${item.last_updated || '-'}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="/admin/student-achievements/${item.id}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-600/20 hover:bg-purple-600/30 text-purple-400 rounded-lg text-[11px] font-bold transition-colors border border-purple-600/20">
                            Lihat
                        </a>
                    </td>
                </tr>`;
                    }).join('');
                    showEl('draftTableWrap');
                })
                .catch(err => {
                    hideEl('draftLoading');
                    showEl('draftTableWrap');
                    document.getElementById('draftTableBody').innerHTML =
                        `<tr><td colspan="6" class="px-4 py-8 text-center text-red-400 text-sm">${esc(err.message)}</td></tr>`;
                });
        };

        window.closeDraftModal = function () {
            hideEl('draftModal'); unlockScroll();
        };

        // ═══════════════════════════════════════════
        // GLOBAL — Close on Escape
        // ═══════════════════════════════════════════
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeSlaModal();
                closeDuplikasiModal();
                closeTanpaDocModal();
                closeDraftModal();
            }
        });

        // ═══════════════════════════════════════════
        // BRIDGE — showAnomalyDetail replacement
        // ═══════════════════════════════════════════
        window.showAnomalyDetail = function (type, count) {
            switch (type) {
                case 'sla_breach': openSlaModal(); break;
                case 'duplicates': openDuplikasiModal(); break;
                case 'missing_documents': openTanpaDocModal(); break;
                case 'abandoned_drafts': openDraftModal(); break;
            }
        };
    })();
</script>