@props(['name' => 'sk_id', 'required' => false, 'error' => null, 'placeholder' => 'Cari SK (Ketik No. SK atau Judul)...', 'selected' => null])

<div x-data="skSearchSelect()" x-init="selectedSk = {{ json_encode($selected) }}" class="relative">
    <label class="block text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-widest mb-3">
        Pilih SK Kolektif <span class="text-gray-400 font-normal lowercase tracking-normal">(opsional)</span>
    </label>

    <!-- Hidden input for form submission -->
    <input type="hidden" name="{{ $name }}" :value="selectedSk ? selectedSk.id : ''">

    <!-- Search Input Area -->
    <div class="relative group">
        <!-- Selected SK display when something is selected -->
        <template x-if="selectedSk">
            <div
                class="w-full bg-emerald-50 dark:bg-emerald-900/20 border-2 border-emerald-500 dark:border-emerald-500 rounded-2xl py-4 px-5 flex items-center justify-between group">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div
                        class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center text-white flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-bold text-emerald-900 dark:text-emerald-100 truncate"
                            x-text="selectedSk.sk_number"></p>
                        <p class="text-[10px] text-emerald-700 dark:text-emerald-400 font-medium truncate"
                            x-text="selectedSk.title"></p>
                    </div>
                </div>
                <button type="button" @click="clearSelection()"
                    class="p-2 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 rounded-xl text-emerald-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>

        <!-- Search Input when nothing is selected -->
        <template x-if="!selectedSk">
            <div class="relative">
                <input type="text" x-model="searchQuery" @keydown.enter.prevent="searchSks()"
                    @focus="showDropdown = true" @click.away="showDropdown = false"
                    placeholder="Ketik No. SK atau Judul lalu tekan Enter..."
                    class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 dark:text-white rounded-2xl py-4 px-12 text-base font-medium focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all shadow-sm {{ $error ? 'border-red-500' : '' }}">

                <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>

                <div x-show="loading" class="absolute right-4 top-1/2 -translate-y-1/2">
                    <svg class="w-5 h-5 text-emerald-600 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
            </div>
        </template>

        <!-- Dropdown Results -->
        <div x-show="showDropdown && !selectedSk && (sks.length > 0 || searchQuery.length >= 2)" x-cloak
            class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">

            <div x-show="loading" class="p-8 text-center">
                <svg class="w-8 h-8 mx-auto text-emerald-500 animate-spin mb-3" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Mencari dokumen SK...</p>
            </div>

            <div x-show="!loading && sks.length === 0 && searchQuery.length >= 2" class="p-8 text-center">
                <div
                    class="w-12 h-12 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">SK tidak ditemukan</p>
            </div>

            <div x-show="!loading && sks.length > 0" class="max-h-72 overflow-y-auto custom-scrollbar">
                <template x-for="sk in sks" :key="sk.id">
                    <button type="button" @click="selectSk(sk)"
                        class="w-full px-5 py-4 text-left hover:bg-emerald-50 dark:hover:bg-emerald-900/20 border-b border-gray-100 dark:border-gray-700 last:border-b-0 transition-all flex items-center gap-4 group">
                        <div
                            class="w-10 h-10 bg-gray-100 dark:bg-gray-700 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/40 rounded-xl flex items-center justify-center text-gray-400 group-hover:text-emerald-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="overflow-hidden">
                            <p class="font-bold text-gray-900 dark:text-white truncate" x-text="sk.sk_number"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5" x-text="sk.title"></p>
                        </div>
                    </button>
                </template>
            </div>

            <div x-show="searchQuery.length < 2 && sks.length === 0"
                class="p-5 text-center bg-gray-50 dark:bg-gray-700/30">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Ketik minimal 2 karakter No.
                    SK/Judul</p>
            </div>
        </div>
    </div>

    @if($error)
        <p class="text-red-500 text-xs mt-2 font-medium italic flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            </svg>
            {{ $error }}
        </p>
    @endif
</div>

@once
    @push('scripts')
        <script>
            function skSearchSelect() {
                return {
                    searchQuery: '',
                    sks: [],
                    selectedSk: null,
                    showDropdown: false,
                    loading: false,

                    async searchSks() {
                        const query = this.searchQuery.trim();
                        if (query.length < 2) {
                            this.sks = [];
                            return;
                        }

                        this.loading = true;
                        this.showDropdown = true;

                        try {
                            const response = await fetch(`/api/sigap/sk/search?q=${encodeURIComponent(query)}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                            if (!response.ok) throw new Error('Network error');

                            this.sks = await response.json();
                        } catch (error) {
                            console.error('Error searching SK:', error);
                            this.sks = [];
                        } finally {
                            this.loading = false;
                        }
                    },

                    selectSk(sk) {
                        this.selectedSk = sk;
                        this.searchQuery = '';
                        this.showDropdown = false;
                        this.sks = [];
                    },

                    clearSelection() {
                        this.selectedSk = null;
                        this.searchQuery = '';
                        this.sks = [];
                    }
                }
            }
        </script>
    @endpush
@endonce