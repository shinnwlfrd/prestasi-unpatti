@extends('layouts.app')

@section('title', 'Profil Saya')
@section('subtitle', 'Pengaturan Akun')

@php
    // Get user based on auth type
    // Debug: Check what auth type is active
    $authRole = session('auth_role');
    $isStudent = $authRole === 'student';

    if ($isStudent) {
        $studentId = session('student_id');
        $student = \App\Models\Student::where('student_id', $studentId)->first();

        if (!$student) {
            // Student not found, might be logged in as admin
            $user = auth()->user();
            if (!$user) {
                abort(403, 'Unauthorized access');
            }
            $currentRole = $user->getCurrentRole();
            $user->role_display = $currentRole ? $currentRole->getRoleDisplayName() : 'User';
        } else {
            $user = (object) [
                'name' => $student->name ?? 'Student',
                'email' => $student->email ?? '',
                'role' => 'Mahasiswa',
                'role_display' => 'Mahasiswa',
                'is_active' => true,
                'photo_url' => null,
                'last_login_method' => null,
                'password' => null,
                'provider' => null,
                'linked_at' => null,
                'last_login_at' => null,
            ];
        }
    } else {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized access');
        }

        // Get current role display name
        $currentRole = $user->getCurrentRole();
        $user->role_display = $currentRole ? $currentRole->getRoleDisplayName() : $user->role;
    }
    $userName = $user->name ?? 'User';
@endphp

@section('content')
    <div class="max-w-3xl mx-auto space-y-5 sm:space-y-6 animate-fade-in px-4 sm:px-6 pb-20">
        <!-- Back Button -->
        <div>
            @php
                if (session('auth_role') === 'student') {
                    $backRoute = route('student.dashboard');
                } else {
                    $authUser = auth()->user();
                    $currentRole = $authUser?->getCurrentRole();

                    $backRoute = match ($currentRole?->role ?? 'mahasiswa') {
                        'super_admin', 'admin' => route('admin.dashboard'),
                        'operator' => route('validator.pending.index'),
                        'pimpinan' => route('pimpinan.dashboard'),
                        'mahasiswa' => route('student.dashboard'),
                        default => url()->previous() != url()->current() ? url()->previous() : '/'
                    };
                }
            @endphp
            <a href="{{ $backRoute }}"
                class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>

        <!-- Multi-Role Switcher (if user has multiple roles) -->
        @if($hasMultipleRoles ?? false)
            <x-card>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Multi-Role Account</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Anda memiliki {{ count($availableRoles) }} role
                                yang terhubung</p>
                        </div>
                    </div>
                    <a href="{{ route('role.switch.page') }}"
                        class="w-full sm:w-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium flex items-center gap-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        Ganti Role
                    </a>
                </div>

                <!-- Available Roles List -->
                <div class="mt-4 space-y-2">
                    @foreach($availableRoles as $role)
                        <div
                            class="flex items-center justify-between p-3 sm:p-4 rounded-lg {{ $role['current'] ? 'bg-indigo-50 dark:bg-indigo-900/20 border-2 border-indigo-200 dark:border-indigo-800' : 'bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-gradient-to-br 
                                    @if($role['role'] === 'mahasiswa') from-blue-500 to-cyan-600
                                    @elseif($role['role'] === 'admin') from-blue-500 to-indigo-600
                                    @elseif($role['role'] === 'operator') from-emerald-500 to-teal-600
                                    @elseif($role['role'] === 'pimpinan') from-amber-500 to-orange-600
                                    @else from-gray-500 to-gray-600
                                    @endif
                                    flex items-center justify-center">
                                    @if($role['role'] === 'mahasiswa')
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    @elseif($role['role'] === 'admin')
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    @elseif($role['role'] === 'operator')
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    @elseif($role['role'] === 'pimpinan')
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $role['name'] }}</p>
                                        @if($role['current'])
                                            <span class="px-2 py-0.5 text-xs bg-indigo-600 text-white rounded-full">Aktif</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $role['level'] ?? '' }}
                                        @if(!empty($role['scope']))
                                            • {{ $role['scope'] }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif

        <!-- Profile Card -->
        <x-card>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                <div class="relative">
                    <img src="{{ $user->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff&size=128' }}"
                        alt="Foto Profil"
                        class="w-24 h-24 rounded-xl object-cover border-4 border-indigo-100 dark:border-indigo-900/50 shadow-lg">
                    @if($user->last_login_method === 'sso')
                        <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center border-4 border-white dark:border-slate-800"
                            title="Login via SSO">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $user->name }}</h2>
                    <p class="text-indigo-600 dark:text-indigo-400 font-medium">{{ $user->email }}</p>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span
                            class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                            {{ $user->role_display ?? $user->role }}
                        </span>
                        @if($user->is_active)
                            <span
                                class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                                Aktif
                            </span>
                        @else
                            <span
                                class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                                Nonaktif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </x-card>

        @if(session('auth_role') === 'student' || ($isStudent ?? false))
            @php
                $studentId = session('student_id');
                $student = \App\Models\Student::where('student_id', $studentId)->first();

                // If not in DB, try to get from session (virtual student object) or use a dummy
                if (!$student && session('student_data')) {
                    $studentData = session('student_data');
                    $student = (object) [
                        'student_id' => $studentData['nim'] ?? '-',
                        'faculty' => $studentData['fakultas'] ?? 'Data Belum Tersedia',
                        'department' => $studentData['jurusan'] ?? 'Data Belum Tersedia',
                        'program_study' => $studentData['program_studi'] ?? 'Data Belum Tersedia',
                        'angkatan' => $studentData['angkatan'] ?? '-',
                        'gpa' => $studentData['ipk'] ?? 0
                    ];
                }
            @endphp

            @if($student)
                <!-- Academic Information Card -->
                <x-card title="Informasi Akademik">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Fakultas</p>
                                    <p class="font-semibold text-gray-800 dark:text-white">{{ $student->faculty ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Jurusan</p>
                                    <p class="font-semibold text-gray-800 dark:text-white">{{ $student->department ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Program Studi</p>
                                    <p class="font-semibold text-gray-800 dark:text-white">{{ $student->program_study ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">IPK</p>
                                    <p class="font-bold text-lg text-green-600 dark:text-green-400">
                                        {{ number_format($student->gpa ?? 0, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card>
            @endif
        @endif

        <!-- Authentication Info -->
        <x-card title="Metode Autentikasi">
            <div class="space-y-4">
                <!-- Local Auth Status -->
                <div
                    class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800 dark:text-white">Login Lokal</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Email & Password</p>
                        </div>
                    </div>
                    @if($user->password)
                        <span
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Aktif
                        </span>
                    @else
                        <span
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400">
                            Tidak Tersedia
                        </span>
                    @endif
                </div>

                <!-- SSO Status -->
                <div
                    class="flex items-center justify-between p-4 rounded-xl {{ $user->provider ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600' }} border">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg {{ $user->provider ? 'bg-blue-200 dark:bg-blue-800' : 'bg-gray-200 dark:bg-gray-600' }} flex items-center justify-center">
                            <svg class="w-5 h-5 {{ $user->provider ? 'text-blue-600 dark:text-blue-300' : 'text-gray-600 dark:text-gray-300' }}"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800 dark:text-white">SSO SIAKAD</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @if($user->provider)
                                    Terhubung sejak {{ $user->linked_at?->format('d M Y') ?? 'N/A' }}
                                @else
                                    Single Sign-On
                                @endif
                            </p>
                        </div>
                    </div>
                    @if($user->provider)
                        <span
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Terhubung
                        </span>
                    @elseif(config('sso.gates.sso.enabled'))
                        <a href="{{ route('sso.redirect') }}"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-500 hover:bg-blue-600 text-white transition-colors">
                            Hubungkan
                        </a>
                    @else
                        <span
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400">
                            Tidak Tersedia
                        </span>
                    @endif
                </div>
            </div>
        </x-card>

        <!-- Login Activity -->
        <x-card title="Aktivitas Login Terakhir">
            <div class="space-y-3">
                @if($user->last_login_at)
                    <div class="flex items-center justify-between p-3 sm:p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg {{ $user->last_login_method === 'sso' ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-gray-100 dark:bg-gray-600' }} flex items-center justify-center">
                                @if($user->last_login_method === 'sso')
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white">
                                    {{ $user->last_login_method === 'sso' ? 'Login via SSO' : 'Login Lokal' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $user->last_login_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $user->last_login_at->diffForHumans() }}
                        </span>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Belum ada aktivitas login tercatat.</p>
                @endif
            </div>
        </x-card>

        <!-- SSO Migration Notice -->
        @if(config('sso.migration.deadline') && !$user->provider)
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <div
                        class="flex-shrink-0 w-9 h-9 sm:w-10 sm:h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-amber-800 dark:text-amber-300">Migrasi ke SSO</h4>
                        <p class="text-sm text-amber-600 dark:text-amber-400 mt-1">
                            Sistem akan beralih ke SSO penuh pada
                            {{ \Carbon\Carbon::parse(config('sso.migration.deadline'))->format('d M Y') }}.
                            Silakan hubungkan akun Anda dengan SSO SIAKAD sebelum tanggal tersebut.
                        </p>
                        <a href="{{ route('sso.redirect') }}"
                            class="inline-flex items-center gap-2 px-2 py-1.5 rounded-lg text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                            Hubungkan Sekarang
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection