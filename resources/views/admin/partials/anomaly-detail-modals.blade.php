{{--
Anomaly Detail Modals — Dark Theme
════════════════════════════════════
4 specialized pop-up modals:
1. SLA Breach → red accent
2. Duplikasi → orange/soft-red accent
3. Tanpa Dokumen → green (empty state) / amber
4. Draft → purple accent
--}}

{{-- ══════════════════════════════════════════
MODAL 1 — SLA BREACH
══════════════════════════════════════════ --}}
<div id="slaBreachModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog"
    aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeSlaModal()"></div>
    <div
        class="relative w-full max-w-5xl max-h-[92vh] bg-gray-900 rounded-2xl shadow-[0_25px_60px_rgba(0,0,0,0.5)] border border-gray-800 overflow-hidden flex flex-col animate-modalIn">
        {{-- Header --}}
        <div
            class="flex-shrink-0 px-6 py-5 bg-gradient-to-r from-red-950/80 via-gray-900 to-gray-900 border-b border-red-900/30">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-red-500/15 border border-red-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            SLA Breach
                            <span id="slaTotalBadge"
                                class="hidden px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30"></span>
                        </h2>
                        <p class="text-xs text-gray-400 mt-0.5">Pelanggaran waktu proses verifikasi prestasi</p>
                    </div>
                </div>
                <button onclick="closeSlaModal()" class="p-2 rounded-lg hover:bg-white/5 transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Stats Summary --}}
        <div id="slaStats" class="hidden flex-shrink-0 px-6 py-4 border-b border-gray-800">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-gray-800/60 rounded-xl p-3 border border-gray-700/50">
                    <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Total Breach</p>
                    <p id="slaStat1" class="text-xl font-black text-red-400 mt-1">0</p>
                </div>
                <div class="bg-gray-800/60 rounded-xl p-3 border border-gray-700/50">
                    <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Rata-rata Terlambat</p>
                    <p id="slaStat2" class="text-xl font-black text-orange-400 mt-1">0 hari</p>
                </div>
                <div class="bg-gray-800/60 rounded-xl p-3 border border-gray-700/50">
                    <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Terlama</p>
                    <p id="slaStat3" class="text-xl font-black text-red-300 mt-1">0 hari</p>
                </div>
                <div class="bg-gray-800/60 rounded-xl p-3 border border-gray-700/50">
                    <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">SLA Target</p>
                    <p class="text-xl font-black text-emerald-400 mt-1">7 hari</p>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div id="slaFilters" class="hidden flex-shrink-0 px-6 py-3 border-b border-gray-800 bg-gray-900/50">
            <div class="flex flex-wrap items-center gap-3">
                <select id="slaFilterStatus" onchange="filterSlaTable()"
                    class="bg-gray-800 border border-gray-700 text-gray-300 text-xs rounded-lg px-3 py-2 focus:ring-red-500 focus:border-red-500">
                    <option value="all">Semua Status</option>
                    <option value="critical">Kritis (>14 hari)</option>
                    <option value="warning">Peringatan (8-14 hari)</option>
                </select>
                <input type="text" id="slaSearchInput" onkeyup="filterSlaTable()" placeholder="Cari nama / unit..."
                    class="bg-gray-800 border border-gray-700 text-gray-300 text-xs rounded-lg px-3 py-2 w-48 focus:ring-red-500 focus:border-red-500 placeholder:text-gray-600">
            </div>
        </div>

        {{-- Content --}}
        <div class="flex-1 overflow-y-auto px-6 py-4 custom-scrollbar">
            <div id="slaLoading" class="flex flex-col items-center justify-center py-16">
                <div class="w-10 h-10 border-3 border-gray-700 border-t-red-500 rounded-full animate-spin"></div>
                <p class="mt-4 text-xs text-gray-500 font-medium">Memuat data SLA breach...</p>
            </div>
            <div id="slaTableWrap" class="hidden">
                <div class="overflow-x-auto rounded-xl border border-gray-800">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-800/80 text-gray-400 text-[10px] uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold">ID</th>
                                <th class="px-4 py-3 text-left font-bold">Nama</th>
                                <th class="px-4 py-3 text-left font-bold">Unit</th>
                                <th class="px-4 py-3 text-center font-bold">SLA</th>
                                <th class="px-4 py-3 text-center font-bold">Terlambat</th>
                                <th class="px-4 py-3 text-center font-bold">Status</th>
                                <th class="px-4 py-3 text-center font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="slaTableBody" class="divide-y divide-gray-800/50"></tbody>
                    </table>
                </div>
            </div>
            <div id="slaEmpty" class="hidden flex flex-col items-center justify-center py-16">
                <div
                    class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-white">Tidak Ada Pelanggaran SLA</p>
                <p class="text-xs text-gray-500 mt-1">Semua prestasi diproses dalam target waktu.</p>
            </div>
        </div>

        {{-- Footer Insight --}}
        <div id="slaFooter" class="hidden flex-shrink-0 px-6 py-3 border-t border-gray-800 bg-gray-950/50">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <span id="slaInsight">Memuat insight...</span>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
MODAL 2 — DUPLIKASI
══════════════════════════════════════════ --}}
<div id="duplikasiModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog"
    aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeDuplikasiModal()"></div>
    <div
        class="relative w-full max-w-5xl max-h-[92vh] bg-gray-900 rounded-2xl shadow-[0_25px_60px_rgba(0,0,0,0.5)] border border-gray-800 overflow-hidden flex flex-col animate-modalIn">
        {{-- Header --}}
        <div
            class="flex-shrink-0 px-6 py-5 bg-gradient-to-r from-orange-950/60 via-gray-900 to-gray-900 border-b border-orange-900/20">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-orange-500/15 border border-orange-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            Duplikasi Data
                            <span id="dupTotalBadge"
                                class="hidden px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-500/20 text-orange-400 border border-orange-500/30"></span>
                        </h2>
                        <p class="text-xs text-gray-400 mt-0.5">Data terdeteksi ganda berdasarkan NIM, event, dan level
                        </p>
                    </div>
                </div>
                <button onclick="closeDuplikasiModal()" class="p-2 rounded-lg hover:bg-white/5 transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Detection Info --}}
        <div id="dupDetectionInfo" class="hidden flex-shrink-0 px-6 py-3 border-b border-gray-800 bg-orange-950/10">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-orange-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-orange-300">Sistem Deteksi Otomatis</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Duplikasi dideteksi berdasarkan kecocokan field: <span
                            class="text-orange-400 font-semibold">NIM Mahasiswa</span>, <span
                            class="text-orange-400 font-semibold">Nama Event</span>, dan <span
                            class="text-orange-400 font-semibold">Level Prestasi</span>.</p>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="flex-1 overflow-y-auto px-6 py-4 custom-scrollbar">
            <div id="dupLoading" class="flex flex-col items-center justify-center py-16">
                <div class="w-10 h-10 border-3 border-gray-700 border-t-orange-500 rounded-full animate-spin"></div>
                <p class="mt-4 text-xs text-gray-500 font-medium">Memuat data duplikasi...</p>
            </div>
            <div id="dupContent" class="hidden space-y-5"></div>
            <div id="dupEmpty" class="hidden flex flex-col items-center justify-center py-16">
                <div
                    class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-white">Tidak Ada Duplikasi</p>
                <p class="text-xs text-gray-500 mt-1">Semua data unik dan terverifikasi.</p>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
MODAL 3 — TANPA DOKUMEN
══════════════════════════════════════════ --}}
<div id="tanpaDocModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog"
    aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeTanpaDocModal()"></div>
    <div
        class="relative w-full max-w-4xl max-h-[92vh] bg-gray-900 rounded-2xl shadow-[0_25px_60px_rgba(0,0,0,0.5)] border border-gray-800 overflow-hidden flex flex-col animate-modalIn">
        {{-- Header --}}
        <div
            class="flex-shrink-0 px-6 py-5 bg-gradient-to-r from-amber-950/50 via-gray-900 to-gray-900 border-b border-amber-900/20">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-amber-500/15 border border-amber-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            Tanpa Dokumen
                            <span id="docTotalBadge"
                                class="hidden px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30"></span>
                        </h2>
                        <p class="text-xs text-gray-400 mt-0.5">Prestasi tanpa lampiran dokumen pendukung</p>
                    </div>
                </div>
                <button onclick="closeTanpaDocModal()" class="p-2 rounded-lg hover:bg-white/5 transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="flex-1 overflow-y-auto px-6 py-4 custom-scrollbar">
            <div id="docLoading" class="flex flex-col items-center justify-center py-16">
                <div class="w-10 h-10 border-3 border-gray-700 border-t-amber-500 rounded-full animate-spin"></div>
                <p class="mt-4 text-xs text-gray-500 font-medium">Memuat data...</p>
            </div>
            {{-- Table for data --}}
            <div id="docTableWrap" class="hidden">
                <div class="overflow-x-auto rounded-xl border border-gray-800">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-800/80 text-gray-400 text-[10px] uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold">No</th>
                                <th class="px-4 py-3 text-left font-bold">NIM</th>
                                <th class="px-4 py-3 text-left font-bold">Nama</th>
                                <th class="px-4 py-3 text-left font-bold">Prestasi</th>
                                <th class="px-4 py-3 text-center font-bold">Status</th>
                                <th class="px-4 py-3 text-center font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="docTableBody" class="divide-y divide-gray-800/50"></tbody>
                    </table>
                </div>
            </div>
            {{-- Empty / Success State --}}
            <div id="docEmpty" class="hidden flex flex-col items-center justify-center py-20">
                <div
                    class="w-20 h-20 rounded-full bg-emerald-500/10 border-2 border-emerald-500/20 flex items-center justify-center mb-5 animate-pulse-slow">
                    <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="px-6 py-4 bg-emerald-500/5 border border-emerald-500/10 rounded-xl text-center max-w-sm">
                    <p class="text-base font-bold text-emerald-400">Semua Dokumen Lengkap!</p>
                    <p class="text-xs text-gray-400 mt-2 leading-relaxed">Tidak ada prestasi tanpa dokumen pendukung.
                        Sistem dalam keadaan sehat dan semua data terverifikasi.</p>
                </div>
                <div id="docCompare" class="mt-6 flex items-center gap-4 text-xs text-gray-500">
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        Lebih baik dari bulan sebelumnya
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
MODAL 4 — DRAFT
══════════════════════════════════════════ --}}
<div id="draftModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog"
    aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeDraftModal()"></div>
    <div
        class="relative w-full max-w-5xl max-h-[92vh] bg-gray-900 rounded-2xl shadow-[0_25px_60px_rgba(0,0,0,0.5)] border border-gray-800 overflow-hidden flex flex-col animate-modalIn">
        {{-- Header --}}
        <div
            class="flex-shrink-0 px-6 py-5 bg-gradient-to-r from-purple-950/60 via-gray-900 to-gray-900 border-b border-purple-900/20">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-purple-500/15 border border-purple-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            Draft Terbengkalai
                            <span id="draftTotalBadge"
                                class="hidden px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-500/20 text-purple-400 border border-purple-500/30"></span>
                        </h2>
                        <p class="text-xs text-gray-400 mt-0.5">Draft prestasi yang tidak diperbarui lebih dari 30 hari
                        </p>
                    </div>
                </div>
                <button onclick="closeDraftModal()" class="p-2 rounded-lg hover:bg-white/5 transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="flex-1 overflow-y-auto px-6 py-4 custom-scrollbar">
            <div id="draftLoading" class="flex flex-col items-center justify-center py-16">
                <div class="w-10 h-10 border-3 border-gray-700 border-t-purple-500 rounded-full animate-spin"></div>
                <p class="mt-4 text-xs text-gray-500 font-medium">Memuat draft...</p>
            </div>
            <div id="draftTableWrap" class="hidden">
                <div class="overflow-x-auto rounded-xl border border-gray-800">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-800/80 text-gray-400 text-[10px] uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold">ID</th>
                                <th class="px-4 py-3 text-left font-bold">Nama</th>
                                <th class="px-4 py-3 text-left font-bold">Prestasi</th>
                                <th class="px-4 py-3 text-center font-bold">Terbengkalai</th>
                                <th class="px-4 py-3 text-center font-bold">Terakhir Update</th>
                                <th class="px-4 py-3 text-center font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="draftTableBody" class="divide-y divide-gray-800/50"></tbody>
                    </table>
                </div>
            </div>
            <div id="draftEmpty" class="hidden flex flex-col items-center justify-center py-20">
                <div
                    class="w-20 h-20 rounded-full bg-purple-500/10 border-2 border-purple-500/20 flex items-center justify-center mb-5">
                    <svg class="w-10 h-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-base font-bold text-white">Tidak Ada Draft Terbengkalai</p>
                <p class="text-xs text-gray-500 mt-2">Semua draft dalam status aktif atau telah diselesaikan.</p>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
STYLES
══════════════════════════════════════════ --}}
<style>
    @keyframes modalIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(10px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .animate-modalIn {
        animation: modalIn 0.3s ease-out forwards;
    }

    @keyframes pulse-slow {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    .animate-pulse-slow {
        animation: pulse-slow 3s ease-in-out infinite;
    }
</style>