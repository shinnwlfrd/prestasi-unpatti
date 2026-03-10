{{-- Panduan untuk Admin/Super Admin --}}
<div class="mb-8">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
            <span class="material-icons-round text-red-600 dark:text-red-400 text-2xl">admin_panel_settings</span>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Panduan untuk Admin/Super Admin</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">14 fitur manajemen sistem lengkap</p>
        </div>
    </div>
</div>

{{-- 1. Dashboard Admin --}}
<section id="a-dashboard" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">1</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Dashboard Admin</h3>
        </div>
        <div class="ml-10 space-y-4">
            <div class="grid sm:grid-cols-2 gap-3">
                <div class="p-3 bg-gray-50 dark:bg-slate-700/50 rounded-lg">
                    <p class="font-semibold text-sm text-gray-900 dark:text-white mb-1">📊 Statistik Global</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total mahasiswa, prestasi tervalidasi, pending, statistik per fakultas</p>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-slate-700/50 rounded-lg">
                    <p class="font-semibold text-sm text-gray-900 dark:text-white mb-1">🔍 Anomaly Detection</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Duplicate, suspicious patterns, data inconsistencies</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 2. Validasi Universitas --}}
<section id="a-validation" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">2</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Validasi Tingkat Universitas</h3>
        </div>
        <div class="ml-10 space-y-3">
            <p class="text-sm text-gray-700 dark:text-gray-300">Prestasi yang masuk ke Admin sudah disetujui fakultas (status: "Faculty Approved").</p>
            <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">1</span><span>Klik menu <strong>"University Validation"</strong></span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">2</span><span>Lihat daftar prestasi pending</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">3</span><span>Klik prestasi untuk review detail</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">4</span><span>Klik <strong>"Start Review"</strong></span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">5</span><span>Pilih aksi:</span></li>
            </ol>
            <div class="ml-8 grid sm:grid-cols-3 gap-2 text-sm">
                <div class="p-2.5 bg-green-50 dark:bg-green-900/10 rounded-lg border border-green-100 dark:border-green-800/50 text-center">
                    <span class="material-icons-round text-green-500">check_circle</span>
                    <p class="font-semibold text-green-700 dark:text-green-300 text-xs mt-1">Approve</p>
                </div>
                <div class="p-2.5 bg-red-50 dark:bg-red-900/10 rounded-lg border border-red-100 dark:border-red-800/50 text-center">
                    <span class="material-icons-round text-red-500">cancel</span>
                    <p class="font-semibold text-red-700 dark:text-red-300 text-xs mt-1">Reject</p>
                </div>
                <div class="p-2.5 bg-amber-50 dark:bg-amber-900/10 rounded-lg border border-amber-100 dark:border-amber-800/50 text-center">
                    <span class="material-icons-round text-amber-500">undo</span>
                    <p class="font-semibold text-amber-700 dark:text-amber-300 text-xs mt-1">Return to Faculty</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 3. Bulk Assignment SK --}}
<section id="a-bulk" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">3</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Bulk Assignment SK</h3>
        </div>
        <div class="ml-10 space-y-3">
            <p class="text-sm text-gray-700 dark:text-gray-300">Menghubungkan banyak prestasi dengan SK sekaligus:</p>
            <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">1</span><span>Pilih beberapa prestasi (checkbox)</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">2</span><span>Klik <strong>"Bulk Assign SK"</strong></span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">3</span><span>Pilih SK dari dropdown</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">4</span><span>Konfirmasi assignment</span></li>
            </ol>
        </div>
    </div>
</section>

{{-- 4. Manajemen Mahasiswa --}}
<section id="a-students" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">4</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Manajemen Mahasiswa</h3>
        </div>
        <div class="ml-10 text-sm text-gray-700 dark:text-gray-300">
            <p>Menu <strong>"Students"</strong>: Search (NIM, nama, email), filter fakultas/prodi, detail prestasi, statistics, timeline, GPA, dan export data.</p>
        </div>
    </div>
</section>

{{-- 5. Manajemen Prestasi --}}
<section id="a-achievements" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">5</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Manajemen Prestasi</h3>
        </div>
        <div class="ml-10 space-y-3 text-sm text-gray-700 dark:text-gray-300">
            <p>Menu <strong>"Student Achievements"</strong> menampilkan semua prestasi dengan filter status, kategori, tingkat, periode, dan fakultas.</p>
            <div class="flex flex-wrap gap-2">
                @foreach(['Lihat detail', 'Edit data', 'Hapus (soft delete)', 'Export'] as $action)
                    <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-semibold">{{ $action }}</span>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- 6. Validation Logs --}}
<section id="a-logs" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">6</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Validation Logs</h3>
        </div>
        <div class="ml-10 text-sm text-gray-700 dark:text-gray-300">
            <p>Menu <strong>"Validation Logs"</strong> untuk audit trail: siapa, kapan, keputusan, catatan, dan perubahan status. Filter berdasarkan validator, periode, status, dan fakultas.</p>
            <p class="mt-2 text-gray-500 dark:text-gray-400">Digunakan untuk: Audit & compliance, evaluasi kinerja validator, tracking perubahan data.</p>
        </div>
    </div>
</section>

{{-- 7. Manajemen User --}}
<section id="a-users" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">7</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Manajemen User</h3>
        </div>
        <div class="ml-10 space-y-3">
            <div class="grid sm:grid-cols-2 gap-2 text-sm">
                @foreach([
                    ['Tambah User Baru', 'Buat user dengan role & level akses', 'person_add', 'green'],
                    ['Add Role', 'Tambah role ke user existing', 'badge', 'blue'],
                    ['Edit User', 'Update data user', 'edit', 'amber'],
                    ['Delete User/Role', 'Hapus user atau role', 'delete', 'red']
                ] as [$title, $desc, $icon, $color])
                    <div class="flex items-start gap-2 p-3 bg-{{ $color }}-50 dark:bg-{{ $color }}-900/10 rounded-lg border border-{{ $color }}-100 dark:border-{{ $color }}-800/50">
                        <span class="material-icons-round text-{{ $color }}-500 text-lg">{{ $icon }}</span>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white text-xs">{{ $title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $desc }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- 8. Kategori --}}
<section id="a-categories" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">8</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Manajemen Kategori Prestasi</h3>
        </div>
        <div class="ml-10 text-sm text-gray-700 dark:text-gray-300">
            <p>Menu <strong>"Categories"</strong>: CRUD kategori prestasi (Akademik, Non-Akademik). Create, Edit, Delete (hanya jika tidak ada prestasi terkait).</p>
        </div>
    </div>
</section>

{{-- 9. Level --}}
<section id="a-levels" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">9</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Manajemen Level Prestasi</h3>
        </div>
        <div class="ml-10 text-sm text-gray-700 dark:text-gray-300">
            <p>Menu <strong>"Levels"</strong>: CRUD tingkat prestasi (Lokal, Regional, Nasional, Internasional). Atur nama level, bobot poin, dan deskripsi.</p>
        </div>
    </div>
</section>

{{-- 10. Periode --}}
<section id="a-periods" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">10</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Manajemen Periode Akademik</h3>
        </div>
        <div class="ml-10 space-y-3">
            <p class="text-sm text-gray-700 dark:text-gray-300">Menu <strong>"Academic Periods"</strong>:</p>
            <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <li class="flex items-start gap-2"><span class="material-icons-round text-indigo-500 text-base mt-0.5">check</span><span><strong>Create</strong>: Nama, nama singkat, tanggal mulai/selesai, status</span></li>
                <li class="flex items-start gap-2"><span class="material-icons-round text-indigo-500 text-base mt-0.5">check</span><span><strong>Activate</strong>: Hanya 1 periode aktif sekaligus</span></li>
                <li class="flex items-start gap-2"><span class="material-icons-round text-indigo-500 text-base mt-0.5">check</span><span><strong>Edit/Delete</strong>: Jika tidak ada prestasi terkait</span></li>
            </ol>
        </div>
    </div>
</section>

{{-- 11. SK Management --}}
<section id="a-sk" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">11</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Manajemen SK (Surat Keputusan)</h3>
        </div>
        <div class="ml-10 space-y-3 text-sm text-gray-700 dark:text-gray-300">
            <p>Menu <strong>"SK Documents"</strong>: Upload SK baru (nomor, tanggal, file PDF, periode), lihat daftar SK, preview PDF, process assignment ke prestasi, delete SK.</p>
        </div>
    </div>
</section>

{{-- 12. Submit Prestasi --}}
<section id="a-submit" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">12</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Submit Prestasi untuk Mahasiswa</h3>
        </div>
        <div class="ml-10 space-y-3">
            <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">1</span><span>Klik menu <strong>"Submit Achievement"</strong></span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">2</span><span>Search dan pilih mahasiswa</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">3</span><span>Isi formulir prestasi lengkap</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">4</span><span>Upload dokumen pendukung</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">5</span><span>Submit</span></li>
            </ol>
            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800 text-sm text-green-700 dark:text-green-300 flex items-start gap-2">
                <span class="material-icons-round text-base mt-0.5">bolt</span>
                <span>Prestasi langsung <strong>approved</strong> (bypass validasi) — untuk prestasi massal atau event khusus.</span>
            </div>
        </div>
    </div>
</section>

{{-- 13. Anomaly Detection --}}
<section id="a-anomaly" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">13</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Anomaly Detection</h3>
        </div>
        <div class="ml-10 space-y-3">
            <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">Sistem otomatis mendeteksi anomali:</p>
            <div class="space-y-2">
                <div class="p-3 bg-red-50 dark:bg-red-900/10 rounded-lg border border-red-100 dark:border-red-800/50">
                    <p class="font-semibold text-red-700 dark:text-red-300 text-sm">🔴 Duplicate Submissions</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Prestasi yang sama disubmit beberapa kali (berdasarkan nama, tanggal, mahasiswa)</p>
                </div>
                <div class="p-3 bg-yellow-50 dark:bg-yellow-900/10 rounded-lg border border-yellow-100 dark:border-yellow-800/50">
                    <p class="font-semibold text-yellow-700 dark:text-yellow-300 text-sm">🟡 Suspicious Patterns</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Pola mencurigakan (terlalu banyak prestasi dalam waktu singkat)</p>
                </div>
                <div class="p-3 bg-blue-50 dark:bg-blue-900/10 rounded-lg border border-blue-100 dark:border-blue-800/50">
                    <p class="font-semibold text-blue-700 dark:text-blue-300 text-sm">🔵 Data Inconsistencies</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Data tidak konsisten (tanggal kegiatan di masa depan)</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 14. Export --}}
<section id="a-export" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">14</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Export & Reporting</h3>
        </div>
        <div class="ml-10 space-y-3 text-sm text-gray-700 dark:text-gray-300">
            <div class="grid sm:grid-cols-3 gap-2">
                <div class="p-3 bg-gray-50 dark:bg-slate-700/50 rounded-lg text-center">
                    <span class="material-icons-round text-green-500 text-2xl">grid_on</span>
                    <p class="font-semibold text-xs mt-1">Export Achievements</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Excel/CSV/PDF</p>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-slate-700/50 rounded-lg text-center">
                    <span class="material-icons-round text-blue-500 text-2xl">insights</span>
                    <p class="font-semibold text-xs mt-1">Export Statistics</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Per fakultas/periode</p>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-slate-700/50 rounded-lg text-center">
                    <span class="material-icons-round text-purple-500 text-2xl">summarize</span>
                    <p class="font-semibold text-xs mt-1">Custom Report</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Data pilihan</p>
                </div>
            </div>
        </div>
    </div>
</section>
