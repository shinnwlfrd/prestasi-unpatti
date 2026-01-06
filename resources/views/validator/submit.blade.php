<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Ajukan Prestasi Mahasiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen transition-colors duration-300">
    <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-teal-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">Panel Validator</h1>
            </div>
            <div class="flex items-center gap-4">
                <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700">
                    <svg x-show="!darkMode" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                <span class="text-gray-600 dark:text-gray-300 font-medium">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6 max-w-2xl">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3 mb-6">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Ajukan Prestasi Mahasiswa</h2>
            </div>

            @if($errors->any())
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 p-4 rounded-xl mb-6">
                <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
            @endif

            <form action="{{ route('validator.submit.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Pilih Mahasiswa <span class="text-red-500">*</span>
                    </label>
                    <select name="student_id" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-green-500">
                        <option value="">-- Pilih Mahasiswa --</option>
                        @foreach($students as $student)
                        <option value="{{ $student->student_id }}" {{ old('student_id') == $student->student_id ? 'selected' : '' }}>
                            {{ $student->name }} ({{ $student->student_id }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Kategori Prestasi <span class="text-red-500">*</span>
                    </label>
                    <select name="achievement_id" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-green-500">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($achievements as $achievement)
                        <option value="{{ $achievement->id }}" {{ old('achievement_id') == $achievement->id ? 'selected' : '' }}>{{ $achievement->category }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Event <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="event_name" value="{{ old('event_name') }}" required placeholder="Contoh: Lomba Debat Nasional 2024" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Level <span class="text-red-500">*</span>
                    </label>
                    <select name="level" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-green-500">
                        <option value="">-- Pilih Level --</option>
                        <option value="Universitas" {{ old('level') == 'Universitas' ? 'selected' : '' }}>Universitas</option>
                        <option value="Nasional" {{ old('level') == 'Nasional' ? 'selected' : '' }}>Nasional</option>
                        <option value="Internasional" {{ old('level') == 'Internasional' ? 'selected' : '' }}>Internasional</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Penyelenggara <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="organizer" value="{{ old('organizer') }}" required placeholder="Contoh: Kementerian Pendidikan" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tanggal Event <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="event_date" value="{{ old('event_date') }}" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Deskripsi (Opsional)</label>
                    <textarea name="description" rows="3" placeholder="Jelaskan prestasi yang diraih..." class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-green-500">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Upload Sertifikat/Bukti <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="certificate" required accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-700 dark:file:bg-green-900/30 dark:file:text-green-400">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Format: PDF, JPG, PNG. Maks: 5MB</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-green-500 to-teal-600 hover:from-green-600 hover:to-teal-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all">
                        Ajukan Prestasi
                    </button>
                    <a href="{{ route('validator.dashboard') }}" class="flex-1 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-semibold rounded-xl text-center transition-all">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://instant.page/5.2.0" type="module" integrity="sha384-jnZyxPjiipYXnSU0ber8UYWa/3y+LA2aLGeB5rWGKbgsNJYgLAw0qauPVhSQqxr4"></script>
</body>
</html>
