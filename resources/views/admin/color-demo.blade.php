@extends('layouts.admin')

@section('title', 'Demo Skema Warna Admin')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Demo Skema Warna Admin</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Panduan penggunaan kombinasi warna yang konsisten untuk semua halaman admin
            </p>
        </div>
    </div>

    <!-- Color Palette -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Palet Warna Utama</h3>
        </div>
        <div class="admin-card-body">
            <!-- Primary Colors -->
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Primary (Purple/Violet)</h4>
                <div class="grid grid-cols-5 md:grid-cols-10 gap-2">
                    <div class="text-center">
                        <div class="h-16 rounded-lg bg-primary-50 border border-gray-200 dark:border-gray-700"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-1 block">50</span>
                    </div>
                    <div class="text-center">
                        <div class="h-16 rounded-lg bg-primary-100 border border-gray-200 dark:border-gray-700"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-1 block">100</span>
                    </div>
                    <div class="text-center">
                        <div class="h-16 rounded-lg bg-primary-200"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-1 block">200</span>
                    </div>
                    <div class="text-center">
                        <div class="h-16 rounded-lg bg-primary-300"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-1 block">300</span>
                    </div>
                    <div class="text-center">
                        <div class="h-16 rounded-lg bg-primary-400"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-1 block">400</span>
                    </div>
                    <div class="text-center">
                        <div class="h-16 rounded-lg bg-primary-500"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-1 block">500</span>
                    </div>
                    <div class="text-center">
                        <div class="h-16 rounded-lg bg-primary-600"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-1 block">600</span>
                    </div>
                    <div class="text-center">
                        <div class="h-16 rounded-lg bg-primary-700"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-1 block">700</span>
                    </div>
                    <div class="text-center">
                        <div class="h-16 rounded-lg bg-primary-800"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-1 block">800</span>
                    </div>
                    <div class="text-center">
                        <div class="h-16 rounded-lg bg-primary-900"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-1 block">900</span>
                    </div>
                </div>
            </div>

            <!-- Status Colors -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Success</h4>
                    <div class="h-16 rounded-lg bg-green-500"></div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Warning</h4>
                    <div class="h-16 rounded-lg bg-yellow-500"></div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Danger</h4>
                    <div class="h-16 rounded-lg bg-red-500"></div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Info</h4>
                    <div class="h-16 rounded-lg bg-blue-500"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Buttons -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Buttons</h3>
        </div>
        <div class="admin-card-body">
            <div class="flex flex-wrap gap-3">
                <button class="admin-btn-primary">Primary Button</button>
                <button class="admin-btn-secondary">Secondary Button</button>
                <button class="admin-btn-success">Success Button</button>
                <button class="admin-btn-danger">Danger Button</button>
            </div>
        </div>
    </div>

    <!-- Badges -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Badges</h3>
        </div>
        <div class="admin-card-body">
            <div class="flex flex-wrap gap-3">
                <span class="admin-badge-primary">Primary</span>
                <span class="admin-badge-success">Success</span>
                <span class="admin-badge-warning">Warning</span>
                <span class="admin-badge-danger">Danger</span>
                <span class="admin-badge-info">Info</span>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="admin-stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="admin-stat-label">Total Mahasiswa</div>
                    <div class="admin-stat-value">1,234</div>
                    <div class="text-sm text-green-600 dark:text-green-400 mt-1">
                        <span>↑ 12%</span> dari bulan lalu
                    </div>
                </div>
                <div class="admin-stat-icon bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="admin-stat-label">Prestasi Approved</div>
                    <div class="admin-stat-value">567</div>
                    <div class="text-sm text-green-600 dark:text-green-400 mt-1">
                        <span>↑ 8%</span> dari bulan lalu
                    </div>
                </div>
                <div class="admin-stat-icon bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="admin-stat-label">Pending Review</div>
                    <div class="admin-stat-value">89</div>
                    <div class="text-sm text-yellow-600 dark:text-yellow-400 mt-1">
                        <span>→ 0%</span> tidak berubah
                    </div>
                </div>
                <div class="admin-stat-icon bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="admin-stat-label">Rejected</div>
                    <div class="admin-stat-value">23</div>
                    <div class="text-sm text-red-600 dark:text-red-400 mt-1">
                        <span>↓ 5%</span> dari bulan lalu
                    </div>
                </div>
                <div class="admin-stat-icon bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Example -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Form Elements</h3>
        </div>
        <div class="admin-card-body space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Input Text
                </label>
                <input type="text" class="admin-input" placeholder="Masukkan teks">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Select
                </label>
                <select class="admin-input">
                    <option>Pilih opsi</option>
                    <option>Opsi 1</option>
                    <option>Opsi 2</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Textarea
                </label>
                <textarea class="admin-input" rows="3" placeholder="Masukkan deskripsi"></textarea>
            </div>
        </div>
    </div>

    <!-- Table Example -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Table</h3>
        </div>
        <div class="admin-card-body overflow-x-auto">
            <table class="admin-table">
                <thead class="admin-table-header">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Nama
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Tanggal
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="admin-table-row">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">John Doe</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="admin-badge-success">Approved</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            2024-01-15
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                Edit
                            </button>
                        </td>
                    </tr>
                    <tr class="admin-table-row">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Jane Smith</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="admin-badge-warning">Pending</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            2024-01-16
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                Edit
                            </button>
                        </td>
                    </tr>
                    <tr class="admin-table-row">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Bob Johnson</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="admin-badge-danger">Rejected</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            2024-01-17
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                Edit
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Gradient Examples -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="admin-gradient-primary rounded-xl p-6 text-white">
            <h4 class="font-semibold mb-2">Primary Gradient</h4>
            <p class="text-sm opacity-90">Purple gradient untuk elemen utama</p>
        </div>
        <div class="admin-gradient-success rounded-xl p-6 text-white">
            <h4 class="font-semibold mb-2">Success Gradient</h4>
            <p class="text-sm opacity-90">Green gradient untuk success state</p>
        </div>
        <div class="admin-gradient-warning rounded-xl p-6 text-white">
            <h4 class="font-semibold mb-2">Warning Gradient</h4>
            <p class="text-sm opacity-90">Yellow gradient untuk warning state</p>
        </div>
        <div class="admin-gradient-danger rounded-xl p-6 text-white">
            <h4 class="font-semibold mb-2">Danger Gradient</h4>
            <p class="text-sm opacity-90">Red gradient untuk danger state</p>
        </div>
    </div>
</div>
@endsection
