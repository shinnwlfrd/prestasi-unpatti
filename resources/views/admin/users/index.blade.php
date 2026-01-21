@extends('layouts.admin')
@section('title', 'Kelola Users')
@section('content')
<div class="space-y-6" x-data="{
    showModal: {{ session('showModal') ? 'true' : 'false' }},
    editMode: {{ session('editUserId') ? 'true' : 'false' }},
    userId: {{ session('editUserId') ?? 'null' }},
    form: {
        name: '{{ old('name', '') }}',
        email: '{{ old('email', '') }}',
        password: '',
        role: '{{ old('role', 'Validator') }}',
        faculty: '{{ old('faculty', '') }}',
        is_active: {{ old('is_active', 'true') }}
    },
    faculties: [
        'Fakultas Teknik',
        'Fakultas Ekonomi dan Bisnis',
        'Fakultas Hukum',
        'Fakultas Ilmu Sosial dan Ilmu Politik',
        'Fakultas Pertanian',
        'Fakultas Kedokteran',
        'Fakultas Keguruan dan Ilmu Pendidikan',
        'Fakultas Perikanan dan Ilmu Kelautan',
        'Fakultas MIPA'
    ],
    resetForm() {
        this.editMode = false;
        this.userId = null;
        this.form = {
            name: '',
            email: '',
            password: '',
            role: 'Validator',
            faculty: '',
            is_active: true
        };
    },
    editUser(user) {
        this.editMode = true;
        this.userId = user.id;
        this.form = {
            name: user.name,
            email: user.email,
            password: '',
            role: user.role,
            faculty: user.faculty || '',
            is_active: user.is_active
        };
        this.showModal = true;
    }
}" @keydown.escape.window="showModal = false">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg flex items-center gap-2">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg flex items-center gap-2">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Kelola Users</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Manajemen user admin dan validator</p>
        </div>
        <button @click="showModal = true" 
            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah User
        </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">User</th>
                        <th class="px-6 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Email</th>
                        <th class="px-6 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Role</th>
                        <th class="px-6 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Fakultas</th>
                        <th class="px-6 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Status</th>
                        <th class="px-6 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($users as $u)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $u->name }}</div>
                                    @if($u->provider)
                                        <div class="flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400 mt-0.5">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            Terhubung SSO
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $u->email }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $u->role == 'Admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' }}">
                                {{ $u->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($u->faculty)
                                <span class="text-gray-700 dark:text-gray-300">{{ $u->faculty }}</span>
                            @else
                                <span class="text-gray-400 dark:text-gray-500 text-sm">Semua Fakultas</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($u->is_active)
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                    Aktif
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                @if($u->id !== auth()->id())
                                    <button @click="editUser({{ json_encode($u) }})" 
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm font-medium">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.users.delete', $u) }}" method="POST" 
                                        onsubmit="return confirm('Hapus user {{ $u->name }}?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm font-medium">
                                            Hapus
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm">Anda</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Belum ada user.</td></tr>
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editMode ? 'Edit User' : 'Tambah User'"></h3>
                <button @click="showModal = false; resetForm()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            @if($errors->any())
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 p-3 rounded-lg mb-4 text-sm">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
            @endif

            <form :action="editMode ? '/admin/users/' + userId : '{{ route('admin.users.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="form.name" required 
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" x-model="form.email" required 
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Password <span x-show="!editMode" class="text-red-500">*</span>
                        <span x-show="editMode" class="text-gray-400 text-xs">(Kosongkan jika tidak diubah)</span>
                    </label>
                    <input type="password" name="password" x-model="form.password" :required="!editMode"
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role <span class="text-red-500">*</span></label>
                    <select name="role" x-model="form.role" required 
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white">
                        <option value="Admin">Admin</option>
                        <option value="Validator">Validator</option>
                    </select>
                </div>

                <div x-show="form.role === 'Validator'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Fakultas <span class="text-red-500">*</span>
                    </label>
                    <select name="faculty" x-model="form.faculty" :required="form.role === 'Validator'"
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white">
                        <option value="">-- Pilih Fakultas --</option>
                        <template x-for="faculty in faculties" :key="faculty">
                            <option :value="faculty" x-text="faculty"></option>
                        </template>
                        <option value="Semua Fakultas">Semua Fakultas (Super Validator)</option>
                    </select>
                    <p class="text-xs text-red-500 dark:text-red-400 mt-1">
                        ⚠️ Satu fakultas hanya boleh memiliki satu validator aktif
                    </p>
                </div>

                <div x-show="editMode">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active"
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">User Aktif</span>
                    </label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showModal = false; resetForm()"
                        class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium">
                        <span x-text="editMode ? 'Update' : 'Tambah'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
