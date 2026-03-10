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

        function getStatusDark(status) {
            const s = (status || '').toLowerCase();
            if (s.includes('approved') || s === 'disetujui' || s === 'selesai diverifikasi') return 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/20';
            if (s.includes('rejected') || s === 'ditolak') return 'bg-red-500/20 text-red-400 border border-red-500/20';
            if (s.includes('pending') || s === 'menunggu' || s === 'submitted') return 'bg-amber-500/20 text-amber-400 border border-amber-500/20';
            if (s.includes('review')) return 'bg-blue-500/20 text-blue-400 border border-blue-500/20';
            if (s.includes('revision') || s === 'revisi') return 'bg-purple-500/20 text-purple-400 border border-purple-500/20';
            return 'bg-gray-500/20 text-gray-400 border border-gray-500/20';
        }

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

                    renderSlaCards(items);
                    showEl('slaContentWrap');

                    // Footer insight
                    document.getElementById('slaInsight').textContent =
                        `Rata-rata keterlambatan ${avg} hari kerja. ${items.filter(i => (i.working_days_elapsed || i.business_days || 0) > 14).length} kasus berstatus kritis.`;
                    showEl('slaFooter');
                })
                .catch(err => {
                    hideEl('slaLoading');
                    showEl('slaContentWrap');
                    document.getElementById('slaContentWrap').innerHTML =
                        `<div class="text-center py-8 text-red-600 dark:text-red-400 text-sm font-bold bg-red-500/5 rounded-xl border border-red-500/10">${esc(err.message)} — <button onclick="openSlaModal()" class="underline text-red-500 hover:text-red-400">Coba Lagi</button></div>`;
                });
        };

        function renderSlaCards(items) {
            // Group by student
            const groups = items.reduce((acc, item) => {
                const key = item.student_nim || item.nim || 'unknown';
                if (!acc[key]) {
                    acc[key] = {
                        student_name: item.student_name,
                        student_nim: item.student_nim || item.nim,
                        faculty: item.faculty || item.unit || '-',
                        items: []
                    };
                }
                acc[key].items.push(item);
                return acc;
            }, {});

            const container = document.getElementById('slaContentWrap');
            container.innerHTML = Object.values(groups).map((group, gi) => `
                <div class="sla-group bg-gray-50 dark:bg-gray-800/30 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden mb-6" data-student="${esc(group.student_name || '').toLowerCase()}" data-unit="${esc(group.faculty || '').toLowerCase()}">
                    <div class="px-5 py-4 bg-red-500/[0.03] dark:bg-red-500/5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-red-500/10 dark:bg-red-500/20 text-red-600 dark:text-red-400 flex items-center justify-center text-[10px] font-black">${gi + 1}</span>
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-200">${esc(group.student_name)} <span class="text-[10px] text-gray-500 font-mono">(${esc(group.student_nim)})</span></p>
                                <p class="text-[10px] text-gray-400 font-medium">${esc(group.faculty)}</p>
                            </div>
                        </div>
                    </div>
                    <div class="px-5 py-5 grid grid-cols-1 lg:grid-cols-2 gap-4">
                        ${group.items.map((item, ii) => {
                const d = item.working_days_elapsed || item.business_days || 0;
                const statusLabel = d > 14 ? 'Kritis' : d > 7 ? 'Terlambat' : 'Warning';
                const statusClass = d > 14 ? 'bg-red-500/15 text-red-600 dark:text-red-400' : d > 7 ? 'bg-orange-500/15 text-orange-600 dark:text-orange-400' : 'bg-yellow-500/15 text-yellow-600 dark:text-yellow-400';

                return `
                            <div class="sla-card bg-white dark:bg-gray-900/60 rounded-2xl p-4 border border-gray-100 dark:border-gray-800 hover:border-red-500/30 transition-all shadow-sm" data-days="${d}">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-black text-red-600 dark:text-red-400 uppercase tracking-widest">Breach #${ii + 1}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider ${statusClass}">${statusLabel}</span>
                                </div>
                                <p class="text-xs font-bold text-gray-800 dark:text-gray-200 mb-4 line-clamp-2 h-8" title="${esc(item.achievement_name)}">${esc(item.achievement_name)}</p>
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800/50">
                                    <div class="flex flex-col">
                                        <span class="text-[9px] text-gray-400 uppercase font-black tracking-tighter">Waktu Terlewati</span>
                                        <span class="text-xs font-black text-red-600 dark:text-red-400">${d} Hari Kerja</span>
                                    </div>
                                    <a href="/admin/student-achievements/${item.id}" target="_blank" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-[10px] font-bold transition-all border border-gray-200 dark:border-gray-700/50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Proses
                                    </a>
                                </div>
                            </div>
                        `}).join('')}
                    </div>
                </div>
            `).join('');
        }

        window.filterSlaTable = function () {
            const statusFilter = document.getElementById('slaFilterStatus').value;
            const searchInput = document.getElementById('slaSearchInput').value.toLowerCase();

            const groups = document.querySelectorAll('.sla-group');
            groups.forEach(group => {
                const studentName = group.dataset.student;
                const unitName = group.dataset.unit;
                const cards = group.querySelectorAll('.sla-card');
                let visibleCardsInGroup = 0;

                cards.forEach(card => {
                    const days = parseInt(card.dataset.days || '0');
                    let show = true;

                    if (statusFilter === 'critical' && days <= 14) show = false;
                    if (statusFilter === 'warning' && (days <= 7 || days > 14)) show = false;

                    if (searchInput && !studentName.includes(searchInput) && !unitName.includes(searchInput)) {
                        // Check if achievement name matches? (Not stored in dataset but could be)
                        // For now keep student/unit search
                        show = false;
                    }

                    card.style.display = show ? '' : 'none';
                    if (show) visibleCardsInGroup++;
                });

                group.style.display = visibleCardsInGroup > 0 ? '' : 'none';
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

                        // Action buttons & Batch logic
                        const recordIds = records.map(r => r.id);

                        return `
                <div class="bg-gray-50 dark:bg-gray-800/30 rounded-xl border border-gray-200 dark:border-gray-700/40 overflow-hidden mb-6">
                    <div class="px-5 py-4 bg-red-500/[0.03] dark:bg-red-500/5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-red-500/10 dark:bg-red-500/20 text-red-600 dark:text-red-400 flex items-center justify-center text-sm font-black">${gi + 1}</span>
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-200">${esc(group.student_name)} <span class="text-xs text-gray-500 font-mono">(${esc(group.student_nim || '')})</span></p>
                                <p class="text-[11px] text-gray-500">${esc(group.event_name)} • ${esc(group.level)}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30">${records.length} Record Terdeteksi</span>
                        </div>
                    </div>
                    
                    <div class="px-5 py-5">
                        <div class="grid grid-cols-1 lg:grid-cols-${Math.min(records.length, 3)} gap-4">
                            ${records.map((rec, ri) => `
                            <div class="relative group/card">
                                <div class="h-full bg-white dark:bg-gray-900/60 rounded-2xl p-4 border transition-all duration-300 ${rec.is_oldest ? 'border-red-500/40 bg-red-500/[0.02]' : 'border-gray-100 dark:border-gray-800 hover:border-gray-200 dark:hover:border-gray-700 shadow-sm'}">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full ${rec.is_oldest ? 'bg-red-500 text-white dark:text-gray-900' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500'} flex items-center justify-center text-[10px] font-black">${ri + 1}</span>
                                            <span class="text-[10px] font-black uppercase tracking-widest ${rec.is_oldest ? 'text-red-600 dark:text-red-400' : 'text-gray-400 dark:text-gray-500'}">${rec.is_oldest ? 'Data Utama (Tertua)' : 'Duplikat'}</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-tighter ${getStatusDark(rec.validation_status)}">${esc(rec.validation_status)}</span>
                                    </div>
                                    
                                    <div class="space-y-3 mb-5">
                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-bold text-gray-400 dark:text-gray-600 uppercase tracking-widest">ID Prestasi</span>
                                            <span class="text-xs font-mono text-gray-700 dark:text-gray-300">#${rec.id}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-bold text-gray-400 dark:text-gray-600 uppercase tracking-widest">Waktu Input</span>
                                            <span class="text-xs text-gray-600 dark:text-gray-400">${rec.created_at}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-bold text-gray-400 dark:text-gray-600 uppercase tracking-widest">Dokumen Sertifikat</span>
                                            <span class="text-xs ${rec.has_certificate ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'} font-bold flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${rec.has_certificate ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}"/></svg>
                                                ${rec.has_certificate ? 'Tersedia' : 'Tidak Ada'}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2 pt-4 border-t border-gray-100 dark:border-gray-800/50">
                                        <a href="/admin/student-achievements/${rec.id}" target="_blank" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-[10px] font-bold transition-all border border-gray-200 dark:border-gray-700/50">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Detail
                                        </a>
                                        <button onclick="deleteRecord(${rec.id})" class="inline-flex items-center justify-center w-10 h-10 bg-red-50 dark:bg-red-600/10 hover:bg-red-100 dark:hover:bg-red-600/20 text-red-600 dark:text-red-400 rounded-xl transition-all border border-red-200 dark:border-red-600/20" title="Hapus Item Ini">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="absolute -top-2 left-1/2 -translate-x-1/2 opacity-0 group-hover/card:opacity-100 transition-opacity pointer-events-none">
                                    <div class="bg-white dark:bg-gray-800 px-3 py-1 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 text-[9px] font-bold text-gray-600 dark:text-gray-300 whitespace-nowrap">Keep This Record</div>
                                </div>
                            </div>
                            `).join('')}
                        </div>
                        
                        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 p-4 bg-red-50 dark:bg-red-500/5 rounded-2xl border border-red-100 dark:border-red-500/10">
                            <div class="flex items-center gap-3 text-red-600 dark:text-red-400/80">
                                <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-[11px] font-medium italic">Sistem merekomendasikan untuk menyimpan <span class="font-bold">Data Utama (Tertua)</span> dan menghapus duplikat lainnya.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button onclick="deleteDuplicatesExcept(${records.find(r => r.is_oldest).id}, [${recordIds.join(',')}])" 
                                    class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl text-xs font-black transition-all shadow-lg shadow-red-500/20 active:scale-95 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    BERSIHKAN DUPLIKAT (Batch)
                                </button>
                            </div>
                        </div>
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

        window.deleteDuplicatesExcept = function (keepId, allIds) {
            const toDelete = allIds.filter(id => id !== keepId);
            if (!toDelete.length) return;

            if (!confirm(`Apakah Anda yakin ingin menghapus ${toDelete.length} record duplikat dan hanya menyisakan data utama (#${keepId})?`)) {
                return;
            }

            // Using sequential deletion to avoid race conditions or use a batch API if available
            // For now, call deleteRecord for each
            let current = 0;
            const total = toDelete.length;

            const processNext = () => {
                if (current >= total) {
                    showNotification('success', `${total} data duplikat berhasil dibersihkan.`);
                    openDuplikasiModal(); // Refresh
                    return;
                }

                const recordId = toDelete[current];
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                fetch(`/admin/api/achievements/${recordId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            current++;
                            processNext();
                        } else {
                            throw new Error(data.message || 'Gagal menghapus #' + recordId);
                        }
                    })
                    .catch(err => {
                        showNotification('error', 'Berhenti di ID ' + recordId + ': ' + err.message);
                    });
            };

            processNext();
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
            hideEl('docContentWrap'); hideEl('docEmpty');

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

                    // Group by student
                    const groups = items.reduce((acc, item) => {
                        const key = item.student_nim || item.nim;
                        if (!acc[key]) {
                            acc[key] = {
                                student_name: item.student_name,
                                student_nim: item.student_nim || item.nim,
                                items: []
                            };
                        }
                        acc[key].items.push(item);
                        return acc;
                    }, {});

                    const container = document.getElementById('docContentWrap');
                    container.innerHTML = Object.values(groups).map((group, gi) => `
                        <div class="bg-gray-50 dark:bg-gray-800/30 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden mb-6">
                            <div class="px-5 py-4 bg-red-500/[0.03] dark:bg-red-500/5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-red-500/10 dark:bg-red-500/20 text-red-600 dark:text-red-400 flex items-center justify-center text-sm font-black">${gi + 1}</span>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-gray-200">${esc(group.student_name)} <span class="text-xs text-gray-500 font-mono">(${esc(group.student_nim)})</span></p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30">${group.items.length} Prestasi</span>
                            </div>
                            <div class="px-5 py-5 grid grid-cols-1 lg:grid-cols-2 gap-4">
                                ${group.items.map((item, ii) => `
                                    <div class="bg-white dark:bg-gray-900/60 rounded-2xl p-4 border border-gray-100 dark:border-gray-800 hover:border-red-500/30 transition-all shadow-sm">
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="text-[10px] font-black text-red-600 dark:text-red-400 uppercase tracking-widest">Item #${ii + 1}</span>
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase bg-red-500/15 text-red-500 dark:text-red-400">Missing Files</span>
                                        </div>
                                        <p class="text-xs font-bold text-gray-800 dark:text-gray-200 mb-4 line-clamp-2 h-8" title="${esc(item.achievement_name)}">${esc(item.achievement_name)}</p>
                                        <div class="flex items-center gap-2 pt-4 border-t border-gray-100 dark:border-gray-800/50">
                                            <a href="/admin/student-achievements/${item.id}" target="_blank" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-[10px] font-bold transition-all border border-gray-200 dark:border-gray-700/50">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Lihat & Perbaiki
                                            </a>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `).join('');
                    showEl('docContentWrap');
                })
                .catch(err => {
                    hideEl('docLoading');
                    showEl('docContentWrap');
                    document.getElementById('docContentWrap').innerHTML =
                        `<div class="text-center py-8 text-red-400 text-sm font-bold bg-red-500/5 rounded-xl border border-red-500/10">${esc(err.message)}</div>`;
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
            hideEl('draftContentWrap'); hideEl('draftEmpty');

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

                    // Group by student
                    const groups = items.reduce((acc, item) => {
                        const key = item.student_nim || item.nim;
                        if (!acc[key]) {
                            acc[key] = {
                                student_name: item.student_name,
                                student_nim: item.student_nim || item.nim,
                                items: []
                            };
                        }
                        acc[key].items.push(item);
                        return acc;
                    }, {});

                    const container = document.getElementById('draftContentWrap');
                    container.innerHTML = Object.values(groups).map((group, gi) => `
                        <div class="bg-gray-50 dark:bg-gray-800/30 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden mb-6">
                            <div class="px-5 py-4 bg-red-500/[0.03] dark:bg-red-500/5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-red-500/10 dark:bg-red-500/20 text-red-600 dark:text-red-400 flex items-center justify-center text-sm font-black">${gi + 1}</span>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-gray-200">${esc(group.student_name)} <span class="text-xs text-gray-500 font-mono">(${esc(group.student_nim)})</span></p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30">${group.items.length} Draft</span>
                            </div>
                            <div class="px-5 py-5 grid grid-cols-1 lg:grid-cols-2 gap-4">
                                ${group.items.map((item, ii) => {
                        const d = item.days_abandoned || 0;
                        const dClass = d > 60 ? 'bg-red-500/15 text-red-500' : d > 45 ? 'bg-orange-500/15 text-orange-500' : 'bg-red-500/15 text-red-500';
                        return `
                                    <div class="bg-white dark:bg-gray-900/60 rounded-2xl p-4 border border-gray-100 dark:border-gray-800 hover:border-red-500/30 transition-all shadow-sm">
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="text-[10px] font-black text-red-600 dark:text-red-400 uppercase tracking-widest">Draft #${ii + 1}</span>
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase ${dClass}">${d} h Terbengkalai</span>
                                        </div>
                                        <p class="text-xs font-bold text-gray-800 dark:text-gray-200 mb-4 line-clamp-2 h-8" title="${esc(item.achievement_name)}">${esc(item.achievement_name)}</p>
                                        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800/50">
                                            <span class="text-[10px] text-gray-500">Update: ${item.last_updated || item.updated_at || '-'}</span>
                                            <a href="/admin/student-achievements/${item.id}" target="_blank" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-[10px] font-bold transition-all border border-gray-200 dark:border-gray-700/50">
                                                Lihat
                                            </a>
                                        </div>
                                    </div>
                                `}).join('')}
                            </div>
                        </div>
                    `).join('');
                    showEl('draftContentWrap');
                })
                .catch(err => {
                    hideEl('draftLoading');
                    showEl('draftContentWrap');
                    document.getElementById('draftContentWrap').innerHTML =
                        `<div class="text-center py-8 text-red-400 text-sm font-bold bg-red-500/5 rounded-xl border border-red-500/10">${esc(err.message)}</div>`;
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