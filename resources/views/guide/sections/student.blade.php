{{-- Panduan untuk Mahasiswa --}}
<div class="mb-8">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
            <span class="material-icons-round text-blue-600 dark:text-blue-400 text-2xl">school</span>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Panduan untuk Mahasiswa</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">7 fitur utama untuk mengelola prestasi Anda</p>
        </div>
    </div>
</div>

{{-- 1. Login --}}
<section id="s-login" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="step-number">1</span>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Login ke Sistem</h3>
            </div>
            <ol class="space-y-3 text-gray-700 dark:text-gray-300 ml-10">
                <li class="flex items-start gap-2">
                    <span class="material-icons-round text-indigo-500 text-lg mt-0.5 flex-shrink-0">chevron_right</span>
                    <span>Buka halaman login sistem di <code
                            class="px-2 py-0.5 bg-gray-100 dark:bg-slate-700 rounded text-sm font-mono">{{ url('/login') }}</code></span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="material-icons-round text-indigo-500 text-lg mt-0.5 flex-shrink-0">chevron_right</span>
                    <span>Klik tombol <strong class="text-indigo-600 dark:text-indigo-400">"Login dengan SSO
                            Unpatti"</strong></span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="material-icons-round text-indigo-500 text-lg mt-0.5 flex-shrink-0">chevron_right</span>
                    <span>Masukkan username dan password SSO Unpatti Anda</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="material-icons-round text-indigo-500 text-lg mt-0.5 flex-shrink-0">chevron_right</span>
                    <span>Sistem akan otomatis mengarahkan ke dashboard mahasiswa</span>
                </li>
            </ol>
            <div
                class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800 ml-10">
                <div class="flex items-start gap-2 text-sm text-blue-700 dark:text-blue-300">
                    <span class="material-icons-round text-base mt-0.5">info</span>
                    <span>Jika Anda memiliki multiple role, sistem akan menampilkan halaman pilih role setelah
                        login.</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 2. Dashboard --}}
<section id="s-dashboard" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="step-number">2</span>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Dashboard Mahasiswa</h3>
            </div>
            <p class="text-gray-700 dark:text-gray-300 mb-4 ml-10">Dashboard menampilkan ringkasan lengkap prestasi
                Anda:</p>
            <div class="grid sm:grid-cols-2 gap-3 ml-10">
                <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-slate-700/50 rounded-lg">
                    <span class="material-icons-round text-indigo-500 text-xl">analytics</span>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">Ringkasan Prestasi</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total prestasi, status validasi</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-slate-700/50 rounded-lg">
                    <span class="material-icons-round text-purple-500 text-xl">bar_chart</span>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">Grafik Prestasi</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Per kategori dan tingkat</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-slate-700/50 rounded-lg">
                    <span class="material-icons-round text-blue-500 text-xl">list_alt</span>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">Daftar Prestasi Terbaru</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">5 prestasi terakhir diajukan</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-slate-700/50 rounded-lg">
                    <span class="material-icons-round text-amber-500 text-xl">notifications</span>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">Notifikasi</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Status validasi prestasi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 3. Submit Prestasi --}}
<section id="s-submit" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="step-number">3</span>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Mengajukan Prestasi Baru</h3>
            </div>
            <ol class="space-y-3 text-gray-700 dark:text-gray-300 ml-10">
                <li class="flex items-start gap-2">
                    <span class="material-icons-round text-indigo-500 text-lg mt-0.5 flex-shrink-0">chevron_right</span>
                    <span>Klik menu <strong>"Submit Prestasi"</strong> di sidebar</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="material-icons-round text-indigo-500 text-lg mt-0.5 flex-shrink-0">chevron_right</span>
                    <span>Isi formulir dengan lengkap:</span>
                </li>
            </ol>
            <div class="ml-16 mt-2 grid sm:grid-cols-2 gap-2">
                @foreach(['Nama Kegiatan/Lomba', 'Kategori Prestasi (Akademik/Non-Akademik)', 'Tingkat (Lokal/Regional/Nasional/Internasional)', 'Penyelenggara', 'Tanggal Kegiatan', 'Peringkat/Pencapaian', 'Periode Akademik'] as $field)
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <span class="material-icons-round text-green-500 text-base">check_circle_outline</span>
                        <span>{{ $field }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 ml-10">
                <div class="flex items-start gap-2">
                    <span class="material-icons-round text-indigo-500 text-lg mt-0.5 flex-shrink-0">chevron_right</span>
                    <span class="text-gray-700 dark:text-gray-300">Klik <strong
                            class="text-indigo-600 dark:text-indigo-400">"Simpan & Lanjut ke Upload
                            Dokumen"</strong></span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 4. Upload Dokumen --}}
<section id="s-upload" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="step-number">4</span>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Upload Dokumen Pendukung</h3>
            </div>

            <div class="ml-10 space-y-4">
                <p class="font-semibold text-gray-900 dark:text-white">Dokumen yang Wajib Diunggah:</p>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div class="p-4 bg-red-50 dark:bg-red-900/10 rounded-lg border border-red-100 dark:border-red-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-icons-round text-red-500">description</span>
                            <span class="font-semibold text-red-700 dark:text-red-300">Sertifikat/Piagam</span>
                            <span
                                class="text-xs px-2 py-0.5 bg-red-200 dark:bg-red-800 text-red-800 dark:text-red-200 rounded-full font-bold">WAJIB</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Format: PDF, JPG, PNG • Max: 5MB</p>
                    </div>
                    <div
                        class="p-4 bg-gray-50 dark:bg-slate-700/50 rounded-lg border border-gray-200 dark:border-slate-600">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-icons-round text-gray-500">photo_library</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Dokumentasi Kegiatan</span>
                            <span
                                class="text-xs px-2 py-0.5 bg-gray-200 dark:bg-slate-600 text-gray-600 dark:text-gray-300 rounded-full">Opsional</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Foto kegiatan, poster • Max: 5MB/file</p>
                    </div>
                </div>

                <p class="font-semibold text-gray-900 dark:text-white mt-4">Cara Upload:</p>
                <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                    <li class="flex items-start gap-2">
                        <span class="step-number text-xs w-6 h-6">1</span>
                        <span>Klik <strong>"Pilih File"</strong> atau drag & drop file</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="step-number text-xs w-6 h-6">2</span>
                        <span>Tunggu hingga upload selesai (progress bar hijau)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="step-number text-xs w-6 h-6">3</span>
                        <span>Ulangi untuk dokumen tambahan</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="step-number text-xs w-6 h-6">4</span>
                        <span>Klik <strong class="text-indigo-600 dark:text-indigo-400">"Submit Semua
                                Dokumen"</strong></span>
                    </li>
                </ol>

                <div class="mt-4">
                    <p class="font-semibold text-gray-900 dark:text-white mb-2">Status Dokumen:</p>
                    <div class="flex flex-wrap gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300 rounded-full text-xs font-semibold border border-yellow-200 dark:border-yellow-800">
                            <span class="w-2 h-2 bg-yellow-400 rounded-full"></span> Pending
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-full text-xs font-semibold border border-blue-200 dark:border-blue-800">
                            <span class="w-2 h-2 bg-blue-400 rounded-full"></span> Submitted
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-full text-xs font-semibold border border-green-200 dark:border-green-800">
                            <span class="w-2 h-2 bg-green-400 rounded-full"></span> Approved
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-full text-xs font-semibold border border-red-200 dark:border-red-800">
                            <span class="w-2 h-2 bg-red-400 rounded-full"></span> Rejected
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 5. Status Prestasi --}}
<section id="s-status" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="step-number">5</span>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Melihat Status Prestasi</h3>
            </div>
            <div class="ml-10 space-y-3">
                <p class="text-gray-700 dark:text-gray-300 mb-4">Status validasi prestasi Anda ditampilkan di dashboard:
                </p>
                <div class="space-y-2">
                    @php
                        $statuses = [
                            ['Submitted', 'Menunggu validasi fakultas', 'schedule', 'yellow'],
                            ['Faculty Review', 'Sedang direview fakultas', 'visibility', 'blue'],
                            ['Faculty Approved', 'Disetujui fakultas, menunggu universitas', 'thumb_up', 'cyan'],
                            ['University Review', 'Sedang direview universitas', 'visibility', 'purple'],
                            ['University Approved', 'Prestasi disetujui ✅', 'check_circle', 'green'],
                            ['Rejected', 'Ditolak (lihat catatan untuk revisi) ❌', 'cancel', 'red'],
                        ];
                    @endphp
                    @foreach($statuses as [$label, $desc, $icon, $color])
                        <div
                            class="flex items-center gap-3 p-2.5 rounded-lg bg-{{ $color }}-50 dark:bg-{{ $color }}-900/10 border border-{{ $color }}-100 dark:border-{{ $color }}-800/50">
                            <span class="material-icons-round text-{{ $color }}-500 text-lg">{{ $icon }}</span>
                            <div>
                                <span class="font-semibold text-sm text-gray-900 dark:text-white">{{ $label }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">— {{ $desc }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 6. Revisi Dokumen --}}
<section id="s-revision" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="step-number">6</span>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Revisi Dokumen yang Ditolak</h3>
            </div>
            <div class="ml-10">
                <p class="text-gray-700 dark:text-gray-300 mb-3">Jika prestasi ditolak, ikuti langkah berikut:</p>
                <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                    <li class="flex items-start gap-2">
                        <span class="step-number text-xs w-6 h-6">1</span>
                        <span>Buka detail prestasi yang ditolak</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="step-number text-xs w-6 h-6">2</span>
                        <span>Baca <strong>catatan validator</strong> dengan teliti</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="step-number text-xs w-6 h-6">3</span>
                        <span>Klik <strong>"Lihat Dokumen"</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="step-number text-xs w-6 h-6">4</span>
                        <span>Klik <strong>"Replace"</strong> pada dokumen yang perlu diganti</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="step-number text-xs w-6 h-6">5</span>
                        <span>Upload dokumen baru yang sudah diperbaiki</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="step-number text-xs w-6 h-6">6</span>
                        <span>Klik <strong class="text-indigo-600 dark:text-indigo-400">"Submit Ulang"</strong></span>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</section>

{{-- 7. Request Review Ulang --}}
<section id="s-review" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="step-number">7</span>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Request Review Ulang</h3>
            </div>
            <div class="ml-10">
                <p class="text-gray-700 dark:text-gray-300 mb-3">Jika Anda merasa keputusan penolakan kurang tepat:</p>
                <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                    <li class="flex items-start gap-2">
                        <span class="step-number text-xs w-6 h-6">1</span>
                        <span>Buka detail prestasi yang ditolak</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="step-number text-xs w-6 h-6">2</span>
                        <span>Klik tombol <strong class="text-indigo-600 dark:text-indigo-400">"Request Review
                                Ulang"</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="step-number text-xs w-6 h-6">3</span>
                        <span>Tulis alasan permintaan review</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="step-number text-xs w-6 h-6">4</span>
                        <span>Submit permintaan</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="step-number text-xs w-6 h-6">5</span>
                        <span>Validator akan meninjau ulang prestasi Anda</span>
                    </li>
                </ol>
                <div
                    class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-100 dark:border-amber-800">
                    <div class="flex items-start gap-2 text-sm text-amber-700 dark:text-amber-300">
                        <span class="material-icons-round text-base mt-0.5">warning</span>
                        <span>Fitur ini hanya tersedia untuk prestasi yang ditolak.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>