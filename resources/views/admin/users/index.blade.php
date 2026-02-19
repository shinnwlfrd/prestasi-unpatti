@extends('layouts.admin')
@section('title', 'Kelola Users')
@section('content')
    <div class="space-y-6 px-4 sm:px-6 lg:px-8" x-data="userManagement()" @keydown.escape.window="showModal = false">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Kelola Users</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manajemen hak akses user admin, validator, dan
                    pimpinan unit.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <button @click="showCreateUserModal = true"
                    class="w-full sm:w-auto justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium flex items-center gap-2 transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Tambah User Baru
                </button>
                <button @click="showModal = true"
                    class="w-full sm:w-auto justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium flex items-center gap-2 transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Role
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <form method="GET" action="{{ route('admin.users') }}" class="flex items-center gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                        placeholder="Cari nama, email, role, atau fakultas..."
                        class="pl-10 w-full py-3 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>
                <button type="submit"
                    class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium flex items-center gap-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cari
                </button>
                @if($search)
                    <a href="{{ route('admin.users') }}"
                        class="px-6 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium flex items-center gap-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Mobile View -->
        <div class="md:hidden space-y-4">
            @forelse($users as $u)
                @php
                    $userRoles = $u->activeRoles;
                    $hasStudentAccount = \App\Models\Student::where('email', $u->email)->exists();
                @endphp

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 space-y-4">

                    <!-- Header -->
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-gray-900 dark:text-white truncate">
                                {{ $u->name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                {{ $u->email }}
                            </p>

                            <div class="flex flex-wrap gap-2 mt-1">
                                @if($u->provider && $u->provider_id)
                                    <span class="text-xs text-blue-600 dark:text-blue-400">SSO</span>
                                @elseif($u->provider)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Menunggu SSO</span>
                                @endif

                                @if($hasStudentAccount)
                                    <span class="text-xs text-indigo-600 dark:text-indigo-400">Multi-Role</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Roles -->
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Role & Scope</p>

                        @if($userRoles->count() > 0)
                            <div class="space-y-2">
                                @foreach($userRoles as $userRole)
                                    <div class="p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs font-medium px-2 py-1 rounded
                                                @if($userRole->role === 'super_admin') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400
                                                @elseif($userRole->role === 'admin') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                                @elseif($userRole->role === 'operator') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                                                @elseif($userRole->role === 'pimpinan') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                                                @else bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400
                                                @endif">
                                                {{ $userRole->getRoleDisplayName() }}
                                            </span>

                                            @if($u->id !== auth()->id() && $userRoles->count() > 1)
                                                <form action="{{ route('admin.users.delete-role', ['user' => $u, 'roleId' => $userRole->id]) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Hapus role ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="text-red-600 dark:text-red-400 text-xs">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                            {{ $userRole->getScopeDescription() }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $u->role ?? 'No Role' }}
                            </span>
                        @endif
                    </div>

                    <!-- Status -->
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                            @if($u->is_active)
                                <span class="text-xs px-2 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-full">
                                    Aktif
                                </span>
                            @else
                                <span class="text-xs px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-full">
                                    Nonaktif
                                </span>
                            @endif
                        </div>

                        <!-- Action -->
                        <div>
                            @if($u->id !== auth()->id())
                                <form action="{{ route('admin.users.delete', $u) }}"
                                    method="POST"
                                    onsubmit="return confirm('Hapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="text-red-600 dark:text-red-400 text-sm font-medium">
                                        Hapus
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-400">Anda</span>
                            @endif
                        </div>
                    </div>

                </div>
            @empty
                <div class="text-center py-10 text-gray-500 dark:text-gray-400 text-sm">
                    Belum ada user.
                </div>
            @endforelse

            @if($users->hasPages())
                <div class="pt-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">User</th>
                            <th class="px-6 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Email</th>
                            <th class="px-6 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Role & Scope</th>
                            <th class="px-6 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Status</th>
                            <th class="px-6 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($users as $u)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $u->name }}</div>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                @if($u->provider && $u->provider_id)
                                                    <div class="flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                        SSO
                                                    </div>
                                                @elseif($u->provider)
                                                    <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400"
                                                        title="User belum login via SSO">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Menunggu SSO
                                                    </div>
                                                @endif
                                                @php
                                                    $hasStudentAccount = \App\Models\Student::where('email', $u->email)->exists();
                                                @endphp
                                                @if($hasStudentAccount)
                                                    <div
                                                        class="flex items-center gap-1 text-xs text-indigo-600 dark:text-indigo-400">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                        </svg>
                                                        Multi-Role
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $u->email }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $userRoles = $u->activeRoles;
                                    @endphp
                                    @if($userRoles->count() > 0)
                                        <div class="space-y-2">
                                            @foreach($userRoles as $userRole)
                                                <div
                                                    class="flex items-center justify-between gap-2 p-2 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2">
                                                            <span
                                                                class="px-2 py-0.5 rounded text-xs font-medium 
                                                                                                                                                                                        @if($userRole->role === 'super_admin') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400
                                                                                                                                                                                        @elseif($userRole->role === 'admin') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                                                                                                                                                                        @elseif($userRole->role === 'operator') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                                                                                                                                                                                        @elseif($userRole->role === 'pimpinan') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                                                                                                                                                                                        @else bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400
                                                                                                                                                                                        @endif">
                                                                {{ $userRole->getRoleDisplayName() }}
                                                            </span>
                                                        </div>
                                                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                            {{ $userRole->getScopeDescription() }}
                                                        </div>
                                                    </div>
                                                    @if($u->id !== auth()->id() && $userRoles->count() > 1)
                                                        <form
                                                            action="{{ route('admin.users.delete-role', ['user' => $u, 'roleId' => $userRole->id]) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Hapus role {{ $userRole->getRoleDisplayName() }} dari {{ $u->name }}?')"
                                                            class="inline">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                class="text-red-600 hover:text-red-800 dark:text-red-400 p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20"
                                                                title="Hapus role ini">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span
                                            class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400">
                                            {{ $u->role ?? 'No Role' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($u->is_active)
                                        <span
                                            class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                            Aktif
                                        </span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($u->id !== auth()->id())
                                            <form action="{{ route('admin.users.delete', $u) }}" method="POST"
                                                onsubmit="return confirm('Hapus user {{ $u->name }}?\n\nPeringatan: Semua role user ini akan dihapus!')"
                                                class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm font-medium">
                                                    Hapus User
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-sm">Anda</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Belum ada user.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- Add/Edit Modal -->
        <div x-show="showModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
            @click.self="showModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Role</h3>
                    <button @click="showModal = false; resetForm()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @if($errors->any())
                    <div
                        class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 p-3 rounded-lg mb-4 text-sm">
                        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                    </div>
                @endif

                <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4" @submit="submitForm">
                    @csrf

                    <!-- Email Input (Primary) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email User/Mahasiswa <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" x-model="form.email" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white"
                            @input.debounce.500ms="checkEmailAndLoadData($event.target.value)"
                            placeholder="Masukkan email user atau mahasiswa">

                        <!-- Loading indicator -->
                        <p x-show="checkingEmail" x-cloak
                            class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Memeriksa email...
                        </p>

                        <!-- Existing User data found -->
                        <div x-show="existingUser && !checkingEmail" x-cloak
                            class="mt-2 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                            <p class="text-sm font-medium text-green-800 dark:text-green-300 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                        clip-rule="evenodd" />
                                </svg>
                                User Ditemukan - Tambah Role Baru
                            </p>
                            <div class="mt-2 space-y-1 text-sm text-green-700 dark:text-green-400">
                                <p><strong>Nama:</strong> <span x-text="existingUser?.name"></span></p>
                                <p><strong>Email:</strong> <span x-text="existingUser?.email"></span></p>
                                <p><strong>Role Saat Ini:</strong> <span x-text="existingUser?.role || 'Tidak ada'"></span>
                                </p>
                                <p class="text-xs mt-2 text-green-600 dark:text-green-500">
                                    ℹ️ User ini sudah ada. Anda dapat menambahkan role tambahan (multi-role).
                                </p>
                            </div>
                        </div>

                        <!-- Student data found -->
                        <div x-show="studentData && !checkingEmail" x-cloak
                            class="mt-2 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <p class="text-sm font-medium text-blue-800 dark:text-blue-300 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                Data Mahasiswa Ditemukan
                            </p>
                            <div class="mt-2 space-y-1 text-sm text-blue-700 dark:text-blue-400">
                                <p><strong>Nama:</strong> <span x-text="studentData?.name"></span></p>
                                <p><strong>NIM:</strong> <span x-text="studentData?.student_id"></span></p>
                                <p><strong>Program Studi:</strong> <span x-text="studentData?.program_study || '-'"></span>
                                </p>
                                <p class="text-xs mt-2 text-blue-600 dark:text-blue-500">
                                    ℹ️ Mahasiswa ini belum memiliki role admin/validator. Anda dapat menambahkan role.
                                </p>
                            </div>
                        </div>

                        <!-- Email not found -->
                        <p x-show="!studentData && !existingUser && !checkingEmail && form.email.length > 5 && !editMode"
                            x-cloak class="text-xs text-red-600 dark:text-red-400 mt-1 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Email tidak ditemukan. Pastikan user/mahasiswa sudah terdaftar di sistem.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role <span
                                class="text-red-500">*</span></label>
                        <select name="role" x-model="form.role" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white">
                            <option value="">-- Pilih Role --</option>
                            <option value="Admin">Admin</option>
                            <option value="Validator">Operator/Validator</option>
                            <option value="Pimpinan">Pimpinan</option>
                        </select>
                    </div>

                    <!-- Operator/Validator Fields -->
                    <div x-show="form.role === 'Validator'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Fakultas <span class="text-red-500">*</span>
                        </label>
                        <select name="faculty" x-model="form.faculty" :required="form.role === 'Validator'"
                            :disabled="loadingFaculties"
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white disabled:opacity-50">
                            <option value="">-- Pilih Fakultas --</option>
                            <template x-for="faculty in sigapFaculties" :key="faculty.id || faculty.nama">
                                <option :value="faculty.nama" x-text="faculty.nama"></option>
                            </template>
                            <option value="Semua Fakultas">Semua Fakultas (Super Validator)</option>
                        </select>
                        <p x-show="loadingFaculties" class="text-xs text-gray-500 mt-1">
                            Memuat data fakultas dari SIGAP...
                        </p>
                    </div>

                    <!-- Pimpinan Fields -->
                    <div x-show="form.role === 'Pimpinan'" x-cloak class="space-y-4">
                        <!-- Level Pimpinan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Level Pimpinan <span class="text-red-500">*</span>
                            </label>
                            <select name="pimpinan_level" x-model="form.pimpinan_level" @change="onPimpinanLevelChange()"
                                :required="form.role === 'Pimpinan'"
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white">
                                <option value="">-- Pilih Level --</option>
                                <option value="university">Universitas (Rektor)</option>
                                <option value="faculty">Fakultas (Dekan)</option>
                                <option value="department">Jurusan (Ketua Jurusan)</option>
                                <option value="program_study">Program Studi (Ketua Prodi)</option>
                                <option value="graduate_program">Program Pascasarjana (Direktur PPs)</option>
                            </select>
                        </div>

                        <!-- Hidden Posisi Field (auto-filled based on level) -->
                        <input type="hidden" name="pimpinan_position" x-model="form.pimpinan_position">
                        <input type="hidden" name="faculty_id" x-model="form.faculty_id">
                        <input type="hidden" name="department_id" x-model="form.department_id">
                        <input type="hidden" name="program_study_id" x-model="form.program_study_id">

                        <!-- Fakultas (for faculty, department, program_study level) -->
                        <div x-show="['faculty', 'department', 'program_study'].includes(form.pimpinan_level)">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fakultas <span class="text-red-500">*</span>
                            </label>
                            <select name="pimpinan_faculty" x-model="form.pimpinan_faculty"
                                @change="onPimpinanFacultyChange($event.target.value)"
                                :required="form.role === 'Pimpinan' && ['faculty', 'department', 'program_study'].includes(form.pimpinan_level)"
                                :disabled="loadingFaculties"
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white disabled:opacity-50">
                                <option value="">-- Pilih Fakultas --</option>
                                <template x-for="faculty in sigapFaculties" :key="faculty.id || faculty.nama">
                                    <option :value="faculty.nama" x-text="faculty.nama"
                                        x-show="faculty.nama !== 'Program Pascasarjana'"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Department (for department, program_study level) -->
                        <div x-show="['department', 'program_study'].includes(form.pimpinan_level)">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Jurusan <span class="text-red-500">*</span>
                            </label>
                            <select name="pimpinan_department" x-model="form.pimpinan_department"
                                @change="onPimpinanDepartmentChange($event.target.value)"
                                :required="form.role === 'Pimpinan' && ['department', 'program_study'].includes(form.pimpinan_level)"
                                :disabled="!form.pimpinan_faculty || filteredDepartments.length === 0"
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white disabled:opacity-50">
                                <option value="">-- Pilih Jurusan --</option>
                                <template x-for="dept in filteredDepartments" :key="dept.id">
                                    <option :value="dept.nama" x-text="dept.nama"></option>
                                </template>
                            </select>
                            <p x-show="form.pimpinan_faculty && filteredDepartments.length === 0"
                                class="text-xs text-gray-500 mt-1">
                                Tidak ada data jurusan. Silakan input manual atau pilih fakultas lain.
                            </p>
                        </div>

                        <!-- Program Study (for program_study level only - not for graduate_program) -->
                        <div x-show="form.pimpinan_level === 'program_study'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Program Studi <span class="text-red-500">*</span>
                            </label>
                            <select name="pimpinan_program_study" x-model="form.pimpinan_program_study"
                                @change="onPimpinanProgramChange($event.target.value)"
                                :required="form.role === 'Pimpinan' && form.pimpinan_level === 'program_study'"
                                :disabled="!form.pimpinan_department || filteredPrograms.length === 0"
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white disabled:opacity-50">
                                <option value="">-- Pilih Program Studi --</option>
                                <template x-for="prog in filteredPrograms" :key="prog.id">
                                    <option :value="prog.nama" x-text="prog.nama"></option>
                                </template>
                            </select>
                            <p x-show="form.pimpinan_department && filteredPrograms.length === 0"
                                class="text-xs text-gray-500 mt-1">
                                Tidak ada data program studi. Silakan input manual atau pilih jurusan lain.
                            </p>
                        </div>

                        <!-- Info for Graduate Program -->
                        <div x-show="form.pimpinan_level === 'graduate_program'" x-cloak
                            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                <strong>Program Pascasarjana</strong> mencakup seluruh program magister dan doktor di
                                universitas.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showModal = false; resetForm()" :disabled="submitting"
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Batal
                        </button>
                        <button type="submit" :disabled="submitting || (!studentData && !existingUser)"
                            class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span x-text="submitting ? 'Menyimpan...' : 'Tambah Role'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create New User Modal -->
        <div x-show="showCreateUserModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
            @click.self="showCreateUserModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah User Baru</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Profil akan diambil via SSO saat login
                            pertama</p>
                    </div>
                    <button @click="showCreateUserModal = false; resetCreateUserForm()"
                        class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('admin.users.create-new') }}" method="POST" class="space-y-4"
                    @submit="submittingNewUser = true; console.log('Submitting form with data:', createUserForm)">
                    @csrf
                    <input type="hidden" name="faculty_id" x-model="createUserForm.faculty_id">
                    <input type="hidden" name="department_id" x-model="createUserForm.department_id">
                    <input type="hidden" name="program_study_id" x-model="createUserForm.program_study_id">

                    <!-- Email Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" x-model="createUserForm.email" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white"
                            placeholder="contoh@unpatti.ac.id">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Email Unpatti untuk SSO login
                        </p>
                    </div>

                    <!-- Nama Sementara -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nama Sementara <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" x-model="createUserForm.name" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white"
                            placeholder="Nama lengkap user">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Akan diupdate otomatis dari SSO saat login pertama
                        </p>
                    </div>

                    <!-- Role Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select name="role" x-model="createUserForm.role" required
                            @change="createUserForm.faculty = ''; createUserForm.pimpinan_level = ''"
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white">
                            <option value="">-- Pilih Role --</option>
                            <option value="Admin">Admin</option>
                            <option value="Validator">Operator/Validator</option>
                            <option value="Pimpinan">Pimpinan</option>
                        </select>
                    </div>

                    <!-- Fakultas (for Validator) -->
                    <div x-show="createUserForm.role === 'Validator'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Fakultas <span class="text-red-500">*</span>
                        </label>
                        <select name="validator_faculty" x-model="createUserForm.faculty"
                            :required="createUserForm.role === 'Validator'" :disabled="loadingFaculties"
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white disabled:opacity-50">
                            <option value="">-- Pilih Fakultas --</option>
                            <template x-for="faculty in sigapFaculties" :key="faculty.id || faculty.nama">
                                <option :value="faculty.nama" x-text="faculty.nama"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Pimpinan Fields -->
                    <div x-show="createUserForm.role === 'Pimpinan'" x-cloak class="space-y-4">
                        <!-- Level Pimpinan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Level Pimpinan <span class="text-red-500">*</span>
                            </label>
                            <select name="pimpinan_level" x-model="createUserForm.pimpinan_level"
                                :required="createUserForm.role === 'Pimpinan'"
                                @change="createUserForm.faculty = ''; createUserForm.department = ''; createUserForm.program_study = ''"
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white">
                                <option value="">-- Pilih Level --</option>
                                <option value="university">Universitas (Rektor)</option>
                                <option value="faculty">Fakultas (Dekan)</option>
                                <option value="department">Jurusan (Ketua Jurusan)</option>
                                <option value="program_study">Program Studi (Kepala Prodi)</option>
                            </select>
                        </div>

                        <!-- Fakultas (for faculty/department/program level) -->
                        <div x-show="['faculty', 'department', 'program_study'].includes(createUserForm.pimpinan_level)">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fakultas <span class="text-red-500">*</span>
                            </label>
                            <select name="pimpinan_faculty" x-model="createUserForm.faculty"
                                :required="['faculty', 'department', 'program_study'].includes(createUserForm.pimpinan_level)"
                                :disabled="loadingFaculties"
                                @change="loadDepartmentsForPimpinan(); createUserForm.department = ''; createUserForm.program_study = ''"
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white disabled:opacity-50">
                                <option value="">-- Pilih Fakultas --</option>
                                <template x-for="faculty in sigapFaculties" :key="faculty.id || faculty.nama">
                                    <option :value="faculty.nama" x-text="faculty.nama"
                                        x-show="faculty.nama !== 'Program Pascasarjana'"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Jurusan (for department/program level) -->
                        <div x-show="['department', 'program_study'].includes(createUserForm.pimpinan_level)">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Jurusan <span class="text-red-500">*</span>
                            </label>
                            <select name="pimpinan_department" x-model="createUserForm.department"
                                :required="['department', 'program_study'].includes(createUserForm.pimpinan_level)"
                                :disabled="!createUserForm.faculty || loadingDepartments"
                                @change="loadProgramsForPimpinan(); createUserForm.program_study = ''"
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white disabled:opacity-50">
                                <option value="">-- Pilih Jurusan --</option>
                                <template x-for="dept in pimpinanDepartments" :key="dept.id || dept.nama">
                                    <option :value="dept.nama" x-text="dept.nama"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Program Studi (for program level) -->
                        <div x-show="createUserForm.pimpinan_level === 'program_study'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Program Studi <span class="text-red-500">*</span>
                            </label>
                            <select name="pimpinan_program_study" x-model="createUserForm.program_study"
                                @change="onProgramStudyChange()"
                                :required="createUserForm.pimpinan_level === 'program_study'"
                                :disabled="!createUserForm.department || loadingPrograms"
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white disabled:opacity-50">
                                <option value="">-- Pilih Program Studi --</option>
                                <template x-for="prog in pimpinanPrograms" :key="prog.id || prog.nama">
                                    <option :value="prog.nama" x-text="prog.nama"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Info for University Level -->
                        <div x-show="createUserForm.pimpinan_level === 'university'" x-cloak
                            class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-3">
                            <p class="text-sm text-purple-700 dark:text-purple-300">
                                <strong>Rektor</strong> memiliki akses ke seluruh data prestasi mahasiswa di universitas.
                            </p>
                        </div>

                        <!-- Info for Faculty Level -->
                        <div x-show="createUserForm.pimpinan_level === 'faculty'" x-cloak
                            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                <strong>Dekan</strong> memiliki akses ke data prestasi mahasiswa di fakultasnya.
                            </p>
                        </div>

                        <!-- Info for Department Level -->
                        <div x-show="createUserForm.pimpinan_level === 'department'" x-cloak
                            class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                            <p class="text-sm text-green-700 dark:text-green-300">
                                <strong>Ketua Jurusan</strong> memiliki akses ke data prestasi mahasiswa di jurusannya.
                            </p>
                        </div>

                        <!-- Info for Program Level -->
                        <div x-show="createUserForm.pimpinan_level === 'program'" x-cloak
                            class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                <strong>Kepala Program Studi</strong> memiliki akses ke data prestasi mahasiswa di program
                                studinya.
                            </p>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div class="text-sm text-blue-700 dark:text-blue-300">
                                <p class="font-medium">Tentang SSO Login</p>
                                <ul class="mt-2 space-y-1 text-xs">
                                    <li>• User akan login menggunakan SSO Unpatti</li>
                                    <li>• Profil lengkap diambil otomatis saat login pertama</li>
                                    <li>• Password tidak perlu diinput (menggunakan SSO)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showCreateUserModal = false; resetCreateUserForm()"
                            :disabled="submittingNewUser"
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Batal
                        </button>
                        <button type="submit" :disabled="submittingNewUser"
                            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <svg x-show="submittingNewUser" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span x-text="submittingNewUser ? 'Membuat...' : 'Buat User'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function userManagement() {
            return {
                showModal: {{ session('showModal') ? 'true' : 'false' }},
                showCreateUserModal: false,
                multiRoleDetected: false,
                checkingEmail: false,
                submitting: false,
                submittingNewUser: false,
                studentData: null,
                existingUser: null,
                loadingFaculties: false,
                sigapFaculties: [],
                sigapDepartments: [],
                sigapPrograms: [],
                filteredDepartments: [],
                filteredPrograms: [],
                form: {
                    email: '',
                    role: '',
                    faculty: '',
                    pimpinan_level: '',
                    pimpinan_position: '',
                    pimpinan_faculty: '',
                    pimpinan_department: '',
                    pimpinan_program_study: '',
                    is_active: true
                },
                createUserForm: {
                    email: '',
                    name: '',
                    role: '',
                    faculty: '',
                    department: '',
                    program_study: '',
                    pimpinan_level: '',
                    faculty_id: '',
                    department_id: '',
                    program_study_id: ''
                },
                pimpinanDepartments: [],
                pimpinanPrograms: [],
                loadingDepartments: false,
                loadingPrograms: false,

                async init() {
                    await this.loadSigapData();
                },

                async loadSigapData() {
                    this.loadingFaculties = true;
                    try {
                        const response = await fetch('/api/sigap/hierarchy');

                        if (response.ok) {
                            const result = await response.json();
                            const hierarchy = result.data || [];

                            this.sigapFaculties = hierarchy.map(faculty => ({
                                id: faculty.id,
                                kode: faculty.kode,
                                nama: faculty.nama
                            }));

                            this.sigapDepartments = [];
                            hierarchy.forEach(faculty => {
                                if (faculty.departments && Array.isArray(faculty.departments)) {
                                    faculty.departments.forEach(dept => {
                                        this.sigapDepartments.push({
                                            id: dept.id,
                                            kode: dept.kode,
                                            nama: dept.nama,
                                            faculty_id: faculty.id,
                                            faculty_nama: faculty.nama
                                        });
                                    });
                                }
                            });

                            this.sigapPrograms = [];
                            hierarchy.forEach(faculty => {
                                if (faculty.departments && Array.isArray(faculty.departments)) {
                                    faculty.departments.forEach(dept => {
                                        if (dept.study_programs && Array.isArray(dept.study_programs)) {
                                            dept.study_programs.forEach(prog => {
                                                this.sigapPrograms.push({
                                                    id: prog.id,
                                                    kode: prog.kode,
                                                    nama: prog.nama,
                                                    department_id: dept.id,
                                                    department_nama: dept.nama,
                                                    faculty_id: faculty.id,
                                                    faculty_nama: faculty.nama
                                                });
                                            });
                                        }
                                    });
                                }
                            });

                            console.log('SIGAP Data Loaded:', {
                                faculties: this.sigapFaculties.length,
                                departments: this.sigapDepartments.length,
                                programs: this.sigapPrograms.length
                            });
                        }
                    } catch (error) {
                        console.error('Error loading SIGAP data:', error);
                    }
                    this.loadingFaculties = false;
                },

                onPimpinanLevelChange() {
                    const positionMap = {
                        'university': 'rektor',
                        'faculty': 'dekan',
                        'department': 'ketua_jurusan',
                        'program_study': 'kaprodi',
                        'graduate_program': 'direktur_pps'
                    };
                    this.form.pimpinan_position = positionMap[this.form.pimpinan_level] || '';

                    this.form.pimpinan_faculty = '';
                    this.form.pimpinan_department = '';
                    this.form.pimpinan_program_study = '';
                    this.form.faculty_id = '';
                    this.form.department_id = '';
                    this.form.program_study_id = '';
                    this.filteredDepartments = [];
                    this.filteredPrograms = [];

                    if (this.form.pimpinan_level === 'graduate_program') {
                        this.form.pimpinan_faculty = 'Program Pascasarjana';
                        const gradFaculty = this.sigapFaculties.find(f => f.nama === 'Program Pascasarjana');
                        if (gradFaculty) this.form.faculty_id = gradFaculty.id;
                    }
                },

                onPimpinanFacultyChange(facultyName) {
                    this.form.pimpinan_department = '';
                    this.form.pimpinan_program_study = '';
                    this.form.department_id = '';
                    this.form.program_study_id = '';
                    this.filteredPrograms = [];

                    const selectedFac = this.sigapFaculties.find(f => f.nama === facultyName);
                    this.form.faculty_id = selectedFac ? selectedFac.id : '';

                    this.filteredDepartments = this.sigapDepartments.filter(dept => {
                        return dept.faculty_nama === facultyName;
                    });

                    console.log('Filtered Departments:', this.filteredDepartments.length, 'for faculty:', facultyName);
                },

                onPimpinanDepartmentChange(departmentName) {
                    this.form.pimpinan_program_study = '';
                    this.form.program_study_id = '';

                    const selectedDept = this.sigapDepartments.find(d => d.nama === departmentName && d.faculty_nama === this.form.pimpinan_faculty);
                    this.form.department_id = selectedDept ? selectedDept.id : '';

                    this.filteredPrograms = this.sigapPrograms.filter(prog => {
                        return prog.department_nama === departmentName;
                    });

                    console.log('Filtered Programs:', this.filteredPrograms.length, 'for department:', departmentName);
                },

                onPimpinanProgramChange(programName) {
                    const selectedProg = this.sigapPrograms.find(p => p.nama === programName && p.department_nama === this.form.pimpinan_department);
                    this.form.program_study_id = selectedProg ? selectedProg.id : '';
                },

                loadDepartmentsForPimpinan() {
                    this.loadingDepartments = true;
                    this.pimpinanDepartments = [];
                    this.pimpinanPrograms = [];
                    this.createUserForm.department_id = '';
                    this.createUserForm.program_study_id = '';

                    const selectedFac = this.sigapFaculties.find(f => f.nama === this.createUserForm.faculty);
                    this.createUserForm.faculty_id = selectedFac ? selectedFac.id : '';

                    if (this.createUserForm.faculty) {
                        this.pimpinanDepartments = this.sigapDepartments.filter(dept => {
                            return dept.faculty_nama === this.createUserForm.faculty;
                        });
                        console.log('Loaded Departments for Pimpinan:', this.pimpinanDepartments.length, 'Faculty:', this.createUserForm.faculty);
                    }

                    this.loadingDepartments = false;
                },

                loadProgramsForPimpinan() {
                    this.loadingPrograms = true;
                    this.pimpinanPrograms = [];
                    this.createUserForm.program_study_id = '';

                    const selectedDept = this.pimpinanDepartments.find(d => d.nama === this.createUserForm.department);
                    this.createUserForm.department_id = selectedDept ? selectedDept.id : '';

                    if (this.createUserForm.department) {
                        this.pimpinanPrograms = this.sigapPrograms.filter(prog => {
                            return prog.department_nama === this.createUserForm.department;
                        });
                        console.log('Loaded Programs for Pimpinan:', this.pimpinanPrograms.length, 'Department:', this.createUserForm.department);
                    }

                    this.loadingPrograms = false;
                },

                onProgramStudyChange() {
                    const selectedProg = this.pimpinanPrograms.find(p => p.nama === this.createUserForm.program_study);
                    this.createUserForm.program_study_id = selectedProg ? selectedProg.id : '';
                },

                resetForm() {
                    this.multiRoleDetected = false;
                    this.checkingEmail = false;
                    this.submitting = false;
                    this.studentData = null;
                    this.existingUser = null;
                    this.filteredDepartments = [];
                    this.filteredPrograms = [];
                    this.form = {
                        email: '',
                        role: '',
                        faculty: '',
                        pimpinan_level: '',
                        pimpinan_position: '',
                        pimpinan_faculty: '',
                        pimpinan_department: '',
                        pimpinan_program_study: '',
                        faculty_id: '',
                        department_id: '',
                        program_study_id: '',
                        is_active: true
                    };
                },

                async checkEmailAndLoadData(email) {
                    if (!email || email.length < 5) {
                        this.multiRoleDetected = false;
                        this.checkingEmail = false;
                        this.studentData = null;
                        this.existingUser = null;
                        return;
                    }

                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        this.multiRoleDetected = false;
                        this.checkingEmail = false;
                        this.studentData = null;
                        this.existingUser = null;
                        return;
                    }

                    this.checkingEmail = true;

                    try {
                        const response = await fetch('/api/check-user-data?email=' + encodeURIComponent(email));

                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }

                        const data = await response.json();

                        if (data.exists_in_users) {
                            this.multiRoleDetected = true;
                            this.existingUser = data.user_data;
                            this.studentData = null;
                        } else if (data.exists_in_students) {
                            this.multiRoleDetected = true;
                            this.studentData = data.student_data;
                            this.existingUser = null;
                        } else {
                            this.multiRoleDetected = false;
                            this.studentData = null;
                            this.existingUser = null;
                        }

                        this.checkingEmail = false;
                    } catch (error) {
                        console.error('Error checking email:', error);
                        this.multiRoleDetected = false;
                        this.studentData = null;
                        this.existingUser = null;
                        this.checkingEmail = false;
                    }
                },

                submitForm(event) {
                    this.submitting = true;
                },

                resetCreateUserForm() {
                    this.submittingNewUser = false;
                    this.pimpinanDepartments = [];
                    this.pimpinanPrograms = [];
                    this.createUserForm = {
                        email: '',
                        name: '',
                        role: '',
                        faculty: '',
                        department: '',
                        program_study: '',
                        pimpinan_level: '',
                        faculty_id: '',
                        department_id: '',
                        program_study_id: ''
                    };
                }
            }
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection