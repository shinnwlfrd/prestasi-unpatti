{{-- FAQ & Troubleshooting --}}
<div class="mb-8">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center">
            <span class="material-icons-round text-teal-600 dark:text-teal-400 text-2xl">help_outline</span>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">FAQ & Troubleshooting</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pertanyaan umum dan solusi masalah teknis</p>
        </div>
    </div>
</div>

{{-- FAQ Umum --}}
<section id="faq-umum" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-icons-round text-indigo-500">quiz</span>
                Pertanyaan Umum
            </h3>

            <div class="space-y-3" x-data="{ openFaq: null }">
                @php
                    $faqs = [
                        ['Bagaimana cara login ke sistem?', 'Gunakan SSO Unpatti. Klik tombol "Login dengan SSO Unpatti" dan masukkan username/password SSO Anda.'],
                        ['Saya lupa password SSO, bagaimana?', 'Hubungi UPT TIK Unpatti untuk reset password SSO.'],
                        ['Berapa lama proses validasi prestasi?', 'Validasi Fakultas: maksimal 7 hari kerja. Validasi Universitas: maksimal 7 hari kerja. Total: maksimal 14 hari kerja.'],
                        ['Dokumen apa saja yang wajib diupload?', 'Minimal sertifikat/piagam prestasi. Dokumen pendukung lain (foto kegiatan, poster) bersifat opsional tapi direkomendasikan.'],
                        ['Format file apa yang diterima?', 'PDF, JPG, PNG dengan ukuran maksimal 5MB per file.'],
                        ['Prestasi saya ditolak, apa yang harus dilakukan?', 'Baca catatan validator dengan teliti, perbaiki dokumen sesuai catatan, upload ulang dokumen yang sudah diperbaiki, lalu submit ulang untuk validasi.'],
                        ['Bisakah mengajukan prestasi yang sudah lama?', 'Ya, bisa. Pilih periode akademik yang sesuai saat mengajukan prestasi.'],
                        ['Bagaimana cara melihat status prestasi?', 'Login ke dashboard, semua prestasi Anda akan ditampilkan dengan status masing-masing.'],
                    ];
                @endphp

                @foreach($faqs as $index => [$question, $answer])
                    <div class="border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                        <button @click="openFaq === {{ $index }} ? openFaq = null : openFaq = {{ $index }}"
                            class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                            <span class="font-semibold text-sm text-gray-900 dark:text-white pr-4">{{ $question }}</span>
                            <span class="material-icons-round text-gray-400 transition-transform duration-200 flex-shrink-0"
                                :class="openFaq === {{ $index }} ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        <div x-show="openFaq === {{ $index }}" x-cloak x-transition class="px-4 pb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $answer }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- Troubleshooting --}}
<section id="faq-trouble" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-icons-round text-amber-500">build</span>
                Troubleshooting
            </h3>

            <div class="space-y-4">
                @php
                    $troubles = [
                        [
                            'Tidak bisa login',
                            [
                                'Pastikan menggunakan username/password SSO Unpatti yang benar',
                                'Clear browser cache dan cookies',
                                'Coba browser lain',
                                'Hubungi UPT TIK jika masih bermasalah'
                            ],
                            'lock',
                            'red'
                        ],
                        [
                            'Upload dokumen gagal',
                            [
                                'Periksa ukuran file (maksimal 5MB)',
                                'Periksa format file (hanya PDF, JPG, PNG)',
                                'Periksa koneksi internet',
                                'Coba compress file jika terlalu besar'
                            ],
                            'cloud_off',
                            'amber'
                        ],
                        [
                            'Prestasi tidak muncul di dashboard',
                            [
                                'Refresh halaman (F5)',
                                'Logout dan login kembali',
                                'Periksa filter periode akademik',
                                'Hubungi admin jika masih bermasalah'
                            ],
                            'visibility_off',
                            'blue'
                        ],
                        [
                            'Tidak bisa submit prestasi',
                            [
                                'Pastikan semua field wajib sudah diisi',
                                'Pastikan minimal 1 dokumen sudah diupload',
                                'Periksa koneksi internet',
                                'Coba lagi beberapa saat'
                            ],
                            'block',
                            'purple'
                        ],
                        [
                            'Role tidak sesuai',
                            [
                                'Logout dan login kembali',
                                'Gunakan fitur "Switch Role" jika memiliki multiple roles',
                                'Hubungi admin untuk update role'
                            ],
                            'person_off',
                            'teal'
                        ],
                        [
                            'Data mahasiswa tidak ditemukan',
                            [
                                'Pastikan mahasiswa sudah terdaftar di SIAKAD',
                                'Sync data dari SIGAP',
                                'Hubungi admin sistem'
                            ],
                            'search_off',
                            'gray'
                        ],
                    ];
                @endphp

                @foreach($troubles as [$problem, $solutions, $icon, $color])
                    <div
                        class="p-4 bg-{{ $color }}-50 dark:bg-{{ $color }}-900/10 rounded-xl border border-{{ $color }}-100 dark:border-{{ $color }}-800/50">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-icons-round text-{{ $color }}-500 text-lg">{{ $icon }}</span>
                            <p class="font-bold text-sm text-gray-900 dark:text-white">{{ $problem }}</p>
                        </div>
                        <ul class="ml-7 space-y-1">
                            @foreach($solutions as $solution)
                                <li class="flex items-start gap-1.5 text-sm text-gray-600 dark:text-gray-400">
                                    <span class="material-icons-round text-xs text-{{ $color }}-400 mt-1">arrow_right</span>
                                    <span>{{ $solution }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- Kontak Support --}}
<section id="kontak" class="mb-10 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-icons-round text-green-500">support_agent</span>
                Kontak Support
            </h3>

            <div class="grid sm:grid-cols-2 gap-4">
                <div
                    class="p-5 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl border border-indigo-100 dark:border-indigo-800/50">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-icons-round text-indigo-600 dark:text-indigo-400 text-xl">school</span>
                        <p class="font-bold text-gray-900 dark:text-white">UPT TIK Unpatti</p>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <span class="material-icons-round text-base">email</span>
                            <a href="mailto:tik@unpatti.ac.id"
                                class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">tik@unpatti.ac.id</a>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-icons-round text-base">phone</span>
                            <span>(0911) 123456</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-icons-round text-base">location_on</span>
                            <span>Gedung Rektorat Unpatti</span>
                        </div>
                    </div>
                </div>

                <div
                    class="p-5 bg-gradient-to-br from-green-50 to-teal-50 dark:from-green-900/20 dark:to-teal-900/20 rounded-xl border border-green-100 dark:border-green-800/50">
                    <div class="flex items-center gap-2 mb-3">
                        <span
                            class="material-icons-round text-green-600 dark:text-green-400 text-xl">military_tech</span>
                        <p class="font-bold text-gray-900 dark:text-white">Admin Sistem Prestasi</p>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <span class="material-icons-round text-base">email</span>
                            <a href="mailto:prestasi@unpatti.ac.id"
                                class="hover:text-green-600 dark:hover:text-green-400 transition-colors">prestasi@unpatti.ac.id</a>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-icons-round text-base">phone</span>
                            <span>(0911) 654321</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Role & Permissions Table --}}
            <div class="mt-6">
                <h4 class="font-bold text-gray-900 dark:text-white text-sm mb-3 flex items-center gap-2">
                    <span class="material-icons-round text-indigo-500 text-base">security</span>
                    Tabel Role & Permissions
                </h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-slate-700">
                                <th
                                    class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-300 rounded-tl-lg">
                                    Fitur</th>
                                <th class="px-3 py-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                    Mahasiswa</th>
                                <th class="px-3 py-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                    Validator</th>
                                <th class="px-3 py-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                    Pimpinan</th>
                                <th
                                    class="px-3 py-2 text-center font-semibold text-gray-700 dark:text-gray-300 rounded-tr-lg">
                                    Admin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @php
                                $permissions = [
                                    ['Submit Prestasi', true, true, false, true],
                                    ['Validasi Fakultas', false, true, false, false],
                                    ['Validasi Universitas', false, false, false, true],
                                    ['View Dashboard', true, true, true, true],
                                    ['View Statistics', false, true, true, true],
                                    ['Manage Users', false, false, false, true],
                                    ['Manage Master Data', false, false, false, true],
                                    ['Export Data', false, true, true, true],
                                ];
                            @endphp
                            @foreach($permissions as [$feature, $mhs, $val, $pim, $adm])
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                                    <td class="px-3 py-2 font-medium text-gray-700 dark:text-gray-300">{{ $feature }}</td>
                                    <td class="px-3 py-2 text-center">
                                        {!! $mhs ? '<span class="text-green-500">✅</span>' : '<span class="text-gray-300 dark:text-gray-600">—</span>' !!}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        {!! $val ? '<span class="text-green-500">✅</span>' : '<span class="text-gray-300 dark:text-gray-600">—</span>' !!}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        {!! $pim ? '<span class="text-green-500">✅</span>' : '<span class="text-gray-300 dark:text-gray-600">—</span>' !!}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        {!! $adm ? '<span class="text-green-500">✅</span>' : '<span class="text-gray-300 dark:text-gray-600">—</span>' !!}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>