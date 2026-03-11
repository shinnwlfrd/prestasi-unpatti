@extends('layouts.validator')

@section('title', 'Pengaturan Panel Kendali Eksekutif')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg transform -rotate-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            Pengaturan Panel Kendali Eksekutif
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 ml-13">Konfigurasi ambang batas (threshold) untuk indikator risiko pada dashboard pimpinan.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-r-lg shadow-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <p class="font-medium text-sm">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Settings Form -->
    <form action="{{ route('admin.settings.executive.update') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden transition-all duration-300">
            <div class="p-8">
                <div class="grid gap-8">
                    @foreach($settings as $setting)
                        <div class="space-y-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <label class="block text-sm font-bold text-gray-900 dark:text-white mb-1">
                                        {{ $setting->label }}
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed max-w-lg">
                                        {{ $setting->description }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="relative w-32">
                                        <input type="{{ $setting->input_type }}" 
                                               name="settings[{{ $setting->key }}]" 
                                               value="{{ $setting->value }}"
                                               class="block w-full px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono"
                                               required>
                                        @if($setting->unit)
                                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                                <span class="text-xs font-bold text-gray-400">{{ $setting->unit }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <hr class="border-gray-100 dark:border-gray-700 mt-6">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-8 py-6 bg-gray-50 dark:bg-gray-900/30 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-3 py-2 rounded-lg border border-amber-100 dark:border-amber-800/50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Perubahan akan berdampak langsung pada indikator risiko di semua dashboard pimpinan.</span>
                </div>
                <div class="flex gap-3">
                    <button type="reset" class="px-6 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                        Reset
                    </button>
                    <button type="submit" class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-500/20 transform active:scale-95 transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.4s ease-out;
    }
</style>
@endsection
