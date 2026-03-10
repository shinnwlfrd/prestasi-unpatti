{{-- Panduan untuk Pimpinan --}}
<div class="mb-8">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
            <span class="material-icons-round text-purple-600 dark:text-purple-400 text-2xl">leaderboard</span>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Panduan untuk Pimpinan</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">9 fitur analytics & monitoring (Read-Only)</p>
        </div>
    </div>
</div>

{{-- 1. Dashboard --}}
<section id="p-dashboard" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">1</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Dashboard Pimpinan</h3>
        </div>
        <div class="ml-10 space-y-4">
            <p class="text-gray-700 dark:text-gray-300 text-sm">Dashboard Pimpinan menampilkan analitik komprehensif:</p>

            <div>
                <p class="font-semibold text-gray-900 dark:text-white text-sm mb-2">KPI Utama:</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach(['Total mahasiswa & prestasi', 'Rasio prestasi/mahasiswa', 'Growth rate', 'Prestasi nasional/internasional %', 'Unit aktif vs total'] as $kpi)
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 p-2 bg-gray-50 dark:bg-slate-700/50 rounded-lg">
                            <span class="material-icons-round text-purple-500 text-base">analytics</span>
                            <span>{{ $kpi }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="font-semibold text-gray-900 dark:text-white text-sm mb-2">Grafik & Visualisasi:</p>
                <div class="grid sm:grid-cols-2 gap-2 text-sm">
                    @foreach(['Tren prestasi 6 semester terakhir', 'Distribusi per kategori & tingkat', 'Perbandingan antar unit'] as $chart)
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <span class="material-icons-round text-indigo-500 text-base">bar_chart</span>
                            <span>{{ $chart }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 2. Perbandingan Hierarkis --}}
<section id="p-hierarki" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">2</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Perbandingan Hierarkis</h3>
        </div>
        <div class="ml-10 space-y-3">
            <p class="text-gray-700 dark:text-gray-300 text-sm mb-3">Berdasarkan level jabatan Anda:</p>
            <div class="space-y-2">
                @foreach([
                    ['Rektor', 'Perbandingan antar fakultas', 'corporate_fare'],
                    ['Dekan', 'Perbandingan antar jurusan', 'business'],
                    ['Ketua Jurusan', 'Perbandingan antar prodi', 'apartment'],
                    ['Kaprodi', 'Perbandingan antar angkatan', 'groups']
                ] as [$jabatan, $desc, $icon])
                    <div class="flex items-center gap-3 p-3 bg-purple-50 dark:bg-purple-900/10 rounded-lg border border-purple-100 dark:border-purple-800/50">
                        <span class="material-icons-round text-purple-500">{{ $icon }}</span>
                        <div>
                            <span class="font-semibold text-sm text-gray-900 dark:text-white">{{ $jabatan }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">→ {{ $desc }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                <span class="material-icons-round text-base align-middle text-indigo-500">touch_app</span>
                Klik pada bar chart untuk drill-down: Fakultas → Jurusan → Prodi
            </p>
        </div>
    </div>
</section>

{{-- 3. Ranking Efisiensi --}}
<section id="p-ranking" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">3</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Ranking Efisiensi Unit</h3>
        </div>
        <div class="ml-10 space-y-3">
            <p class="text-gray-700 dark:text-gray-300 text-sm">Sistem menghitung efisiensi berdasarkan jumlah prestasi per mahasiswa, kualitas prestasi, dan konsistensi.</p>
            <div class="flex flex-wrap gap-2 mt-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300 rounded-full text-xs font-bold border border-yellow-200 dark:border-yellow-800">🥇 Top Performer</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-full text-xs font-bold border border-blue-200 dark:border-blue-800">🥈 Good Performance</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300 rounded-full text-xs font-bold border border-orange-200 dark:border-orange-800">🥉 Average</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-full text-xs font-bold border border-red-200 dark:border-red-800">⚠️ Need Improvement</span>
            </div>
        </div>
    </div>
</section>

{{-- 4. Risk Indicators --}}
<section id="p-risk" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">4</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Risk Indicators & Alerts</h3>
        </div>
        <div class="ml-10 space-y-3">
            <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-100 dark:border-amber-800">
                <p class="font-semibold text-amber-700 dark:text-amber-300 text-sm mb-1">⚠️ Warning (Kuning)</p>
                <ul class="text-xs text-amber-600 dark:text-amber-400 space-y-1 ml-4">
                    <li>• Unit belum mencapai target prestasi nasional/internasional</li>
                    <li>• Partisipasi unit aktif di bawah 30%</li>
                </ul>
            </div>
            <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
                <p class="font-semibold text-red-700 dark:text-red-300 text-sm mb-1">🚨 Danger (Merah)</p>
                <ul class="text-xs text-red-600 dark:text-red-400 space-y-1 ml-4">
                    <li>• Penurunan prestasi > 20% dari periode sebelumnya</li>
                    <li>• Bottleneck validasi (> 7 hari)</li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- 5. Filter Periode --}}
<section id="p-filter" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">5</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Filter Periode Akademik</h3>
        </div>
        <div class="ml-10 space-y-3">
            <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">1</span><span>Klik dropdown <strong>"Pilih Periode"</strong></span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">2</span><span>Pilih satu atau beberapa periode akademik</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">3</span><span>Klik <strong class="text-indigo-600 dark:text-indigo-400">"Apply Filter"</strong></span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">4</span><span>Semua data dan grafik akan diupdate</span></li>
            </ol>
        </div>
    </div>
</section>

{{-- 6. Top Students --}}
<section id="p-topstudents" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">6</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Top Students & GPA</h3>
        </div>
        <div class="ml-10 grid sm:grid-cols-2 gap-2 text-sm">
            @foreach(['Top 10 mahasiswa berprestasi', 'GPA mahasiswa berprestasi', 'Distribusi prestasi per angkatan', 'Top GPA per angkatan (Kaprodi)'] as $item)
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400 p-2 bg-gray-50 dark:bg-slate-700/50 rounded-lg">
                    <span class="material-icons-round text-amber-500 text-base">star</span>
                    <span>{{ $item }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- 7. Detail Mahasiswa --}}
<section id="p-detail" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">7</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Melihat Detail Mahasiswa</h3>
        </div>
        <div class="ml-10 text-sm text-gray-700 dark:text-gray-300">
            <p>Menu <strong>"Students"</strong> (Read-only): Daftar mahasiswa, detail prestasi, statistik, dan timeline prestasi di unit Anda.</p>
        </div>
    </div>
</section>

{{-- 8. Pending View --}}
<section id="p-pending" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">8</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Melihat Pending Validasi</h3>
        </div>
        <div class="ml-10 space-y-3">
            <p class="text-sm text-gray-700 dark:text-gray-300">Pimpinan dapat melihat prestasi yang sedang divalidasi (Submitted, Faculty Review, University Review).</p>
            <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-100 dark:border-amber-800">
                <div class="flex items-start gap-2 text-sm text-amber-700 dark:text-amber-300">
                    <span class="material-icons-round text-base mt-0.5">info</span>
                    <span><strong>Tidak dapat melakukan validasi</strong> — hanya tampilan read-only.</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 9. Export --}}
<section id="p-export" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="step-number">9</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Export Data & Laporan</h3>
        </div>
        <div class="ml-10 space-y-3">
            <div class="flex flex-wrap gap-2 mb-3">
                <span class="px-3 py-1.5 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-lg text-xs font-bold border border-green-200 dark:border-green-800">📊 Excel (.xlsx)</span>
                <span class="px-3 py-1.5 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-lg text-xs font-bold border border-red-200 dark:border-red-800">📄 PDF</span>
                <span class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg text-xs font-bold border border-blue-200 dark:border-blue-800">📋 CSV</span>
            </div>
            <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">1</span><span>Klik tombol <strong>"Export"</strong> di pojok kanan atas</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">2</span><span>Pilih format export</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">3</span><span>Pilih data yang ingin diexport</span></li>
                <li class="flex items-start gap-2"><span class="step-number text-xs w-6 h-6">4</span><span>Klik <strong class="text-indigo-600 dark:text-indigo-400">"Download"</strong></span></li>
            </ol>
        </div>
    </div>
</section>
