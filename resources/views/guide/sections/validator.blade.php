{{-- Panduan untuk Operator/Validator Fakultas --}}
<div class="mb-8">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
            <span class="material-icons-round text-amber-600 dark:text-amber-400 text-2xl">verified_user</span>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Panduan untuk Operator/Validator Fakultas</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">10 fitur manajemen validasi prestasi tingkat fakultas</p>
        </div>
    </div>
</div>

{{-- 1. Dashboard --}}
<section id="v-dashboard" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">1</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Login dan Dashboard</h3>
        </div>
        <p class="text-gray-700 dark:text-gray-300 ml-10 mb-4">Setelah login, Anda akan melihat dashboard dengan:</p>
        <div class="grid sm:grid-cols-2 gap-3 ml-10">
            @foreach([
                ['KPI Cards', 'Total mahasiswa, prestasi, pending validasi', 'insights', 'indigo'],
                ['Grafik Statistik', 'Distribusi prestasi per kategori dan tingkat', 'bar_chart', 'purple'],
                ['Tren Prestasi', 'Perkembangan prestasi per semester', 'trending_up', 'blue'],
                ['Top Students', 'Mahasiswa dengan prestasi terbanyak', 'emoji_events', 'amber']
            ] as [$title, $desc, $icon, $color])
                <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-slate-700/50 rounded-lg">
                    <span class="material-icons-round text-{{ $color }}-500 text-xl">{{ $icon }}</span>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $title }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $desc }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- 2. Validasi Prestasi --}}
<section id="v-pending" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">2</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Validasi Prestasi (Menu Pending)</h3>
        </div>
        <div class="ml-10 space-y-3">
            <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <li class="flex items-start gap-2">
                    <span class="step-number text-xs w-6 h-6">1</span>
                    <span>Klik menu <strong>"Pending"</strong> di sidebar</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="step-number text-xs w-6 h-6">2</span>
                    <span>Lihat daftar prestasi yang menunggu validasi</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="step-number text-xs w-6 h-6">3</span>
                    <span>Gunakan filter untuk mempermudah pencarian:</span>
                </li>
            </ol>
            <div class="ml-8 flex flex-wrap gap-2 mt-2">
                <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-semibold border border-indigo-100 dark:border-indigo-800">Filter Kategori</span>
                <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-semibold border border-indigo-100 dark:border-indigo-800">Filter Tingkat</span>
                <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-semibold border border-indigo-100 dark:border-indigo-800">Search Nama/Kegiatan</span>
            </div>
        </div>
    </div>
</section>

{{-- 3. Review Detail --}}
<section id="v-detail" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">3</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Review Detail Prestasi</h3>
        </div>
        <div class="ml-10 space-y-4">
            <p class="text-gray-700 dark:text-gray-300">Informasi yang ditampilkan pada halaman detail:</p>
            <div class="grid sm:grid-cols-2 gap-2 text-sm">
                @foreach(['Data mahasiswa (NIM, nama, prodi)', 'Detail prestasi (nama, kategori, tingkat)', 'Tanggal kegiatan & periode', 'Dokumen pendukung (sertifikat, foto)'] as $info)
                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                        <span class="material-icons-round text-green-500 text-base">check</span>
                        <span>{{ $info }}</span>
                    </div>
                @endforeach
            </div>
            <p class="font-semibold text-gray-900 dark:text-white mt-3">Tombol Aksi:</p>
            <div class="grid sm:grid-cols-2 gap-2">
                @foreach([
                    ['Start Review', 'Mulai proses review', 'play_circle', 'blue'],
                    ['Approve', 'Setujui prestasi', 'check_circle', 'green'],
                    ['Reject', 'Tolak prestasi (wajib isi alasan)', 'cancel', 'red'],
                    ['Request Revision', 'Minta revisi dokumen', 'edit_note', 'amber']
                ] as [$btn, $desc, $icon, $color])
                    <div class="flex items-center gap-2 p-2.5 rounded-lg bg-{{ $color }}-50 dark:bg-{{ $color }}-900/10 border border-{{ $color }}-100 dark:border-{{ $color }}-800/50 text-sm">
                        <span class="material-icons-round text-{{ $color }}-500">{{ $icon }}</span>
                        <div>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $btn }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $desc }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- 4. Approve --}}
<section id="v-approve" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">4</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Menyetujui Prestasi</h3>
        </div>
        <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300 ml-10">
            <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">1</span><span>Klik tombol <strong>"Start Review"</strong> (jika belum)</span></li>
            <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">2</span><span>Periksa semua dokumen dengan teliti</span></li>
            <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">3</span><span>Klik tombol <strong class="text-green-600 dark:text-green-400">"Approve"</strong></span></li>
            <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">4</span><span>Konfirmasi persetujuan</span></li>
            <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">5</span><span>Prestasi akan diteruskan ke <strong>Admin Universitas</strong></span></li>
        </ol>
    </div>
</section>

{{-- 5. Reject --}}
<section id="v-reject" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">5</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Menolak Prestasi</h3>
        </div>
        <div class="ml-10 space-y-4">
            <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">1</span><span>Klik tombol <strong class="text-red-600 dark:text-red-400">"Reject"</strong></span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">2</span><span><strong>Wajib</strong> isi alasan penolakan dengan jelas</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">3</span><span>Klik <strong>"Submit"</strong></span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">4</span><span>Mahasiswa akan menerima notifikasi penolakan</span></li>
            </ol>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800">
                <p class="text-sm font-semibold text-blue-700 dark:text-blue-300 mb-2 flex items-center gap-1">
                    <span class="material-icons-round text-base">lightbulb</span> Tips Penolakan:
                </p>
                <ul class="text-xs text-blue-600 dark:text-blue-400 space-y-1 ml-5">
                    <li>• Berikan alasan yang spesifik dan konstruktif</li>
                    <li>• Jelaskan dokumen mana yang bermasalah</li>
                    <li>• Berikan panduan untuk perbaikan</li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- 6. Request Revision --}}
<section id="v-revision" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">6</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Request Revisi Dokumen</h3>
        </div>
        <div class="ml-10 space-y-3">
            <p class="text-gray-700 dark:text-gray-300 text-sm">Jika dokumen perlu diperbaiki tanpa menolak prestasi:</p>
            <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">1</span><span>Klik tombol <strong>"Request Revision"</strong></span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">2</span><span>Pilih dokumen yang perlu direvisi</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">3</span><span>Tulis catatan revisi yang jelas</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">4</span><span>Submit permintaan</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">5</span><span>Mahasiswa akan menerima notifikasi untuk upload ulang</span></li>
            </ol>
        </div>
    </div>
</section>

{{-- 7. History --}}
<section id="v-history" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">7</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Melihat History Validasi</h3>
        </div>
        <div class="ml-10 space-y-3">
            <p class="text-gray-700 dark:text-gray-300 text-sm">Menu <strong>"History"</strong> menampilkan:</p>
            <div class="grid sm:grid-cols-2 gap-2 text-sm">
                @foreach(['Semua prestasi yang sudah divalidasi', 'Status akhir (Approved/Rejected)', 'Tanggal validasi', 'Validator yang memproses'] as $item)
                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                        <span class="material-icons-round text-green-500 text-base">check</span>
                        <span>{{ $item }}</span>
                    </div>
                @endforeach
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Filter: Berdasarkan status, periode akademik, kategori prestasi</p>
        </div>
    </div>
</section>

{{-- 8. Data Mahasiswa --}}
<section id="v-students" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">8</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Melihat Data Mahasiswa</h3>
        </div>
        <div class="ml-10 space-y-3">
            <p class="text-gray-700 dark:text-gray-300 text-sm">Menu <strong>"Students"</strong> menampilkan daftar mahasiswa di fakultas/prodi Anda beserta total prestasi, detail prestasi, dan statistik.</p>
            <div class="flex flex-wrap gap-2 text-sm">
                <span class="px-3 py-1 bg-gray-100 dark:bg-slate-700 rounded-full text-gray-600 dark:text-gray-400">Search nama/NIM</span>
                <span class="px-3 py-1 bg-gray-100 dark:bg-slate-700 rounded-full text-gray-600 dark:text-gray-400">Filter prodi</span>
                <span class="px-3 py-1 bg-gray-100 dark:bg-slate-700 rounded-full text-gray-600 dark:text-gray-400">Export data</span>
            </div>
        </div>
    </div>
</section>

{{-- 9. Submit untuk Mahasiswa --}}
<section id="v-submit" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">9</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Submit Prestasi atas Nama Mahasiswa</h3>
        </div>
        <div class="ml-10 space-y-3">
            <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">1</span><span>Klik menu <strong>"Submit Prestasi"</strong></span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">2</span><span>Pilih mahasiswa dari dropdown (search by NIM/nama)</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">3</span><span>Isi formulir prestasi</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">4</span><span>Upload dokumen pendukung</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">5</span><span>Submit prestasi</span></li>
            </ol>
            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800 text-sm text-green-700 dark:text-green-300 flex items-start gap-2">
                <span class="material-icons-round text-base mt-0.5">info</span>
                <span>Prestasi yang disubmit oleh validator akan langsung masuk status <strong>"Faculty Approved"</strong>.</span>
            </div>
        </div>
    </div>
</section>

{{-- 10. SK Management --}}
<section id="v-sk" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">10</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Mengelola SK (Surat Keputusan)</h3>
        </div>
        <div class="ml-10 space-y-3">
            <p class="text-gray-700 dark:text-gray-300 text-sm">Menu <strong>"SK Documents"</strong> untuk mengelola SK prestasi:</p>
            <div class="grid sm:grid-cols-2 gap-2 text-sm">
                @foreach(['Lihat daftar SK yang tersedia', 'Preview SK dalam format PDF', 'Lihat prestasi terkait dengan SK', 'Process assignment SK ke prestasi'] as $feature)
                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                        <span class="material-icons-round text-indigo-500 text-base">check_circle</span>
                        <span>{{ $feature }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
