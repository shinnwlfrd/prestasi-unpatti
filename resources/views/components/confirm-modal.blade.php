<!-- Custom Centered Confirm Modal Component -->
<div x-data="confirmManager()" 
     @confirm.window="open($event.detail)"
     class="relative z-[10000]" 
     x-show="isOpen" 
     x-cloak>
    
    <!-- Backdrop with blur -->
    <div x-show="isOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

    <!-- Modal Container -->
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="isOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-200 dark:border-gray-700"
                 @click.away="cancel()">
                
                <div class="px-6 pt-6 pb-4">
                    <div class="sm:flex sm:items-start">
                        <!-- Icon Circle -->
                        <div class="mx-auto flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-full sm:mx-0"
                             :class="config.type === 'danger' ? 'bg-red-100 dark:bg-red-900/30' : 'bg-blue-100 dark:bg-blue-900/30'">
                            
                            <template x-if="config.type === 'danger'">
                                <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </template>
                            
                            <template x-if="config.type !== 'danger'">
                                <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                                </svg>
                            </template>
                        </div>

                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white" x-text="config.title"></h3>
                            <div class="mt-3">
                                <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-normal leading-relaxed" x-text="config.message"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="px-6 py-6 sm:flex sm:flex-row-reverse gap-3 bg-gray-50/50 dark:bg-gray-900/30">
                    <button type="button" 
                            class="inline-flex w-full justify-center rounded-xl px-5 py-3 text-sm font-bold text-white shadow-lg transition-all active:scale-95 sm:w-auto"
                            :class="config.type === 'danger' ? 'bg-red-600 hover:bg-red-700 shadow-red-500/20' : 'bg-primary-600 hover:bg-primary-700 shadow-primary-500/20'"
                            @click="confirmAction()">
                        <span x-text="config.confirmText"></span>
                    </button>
                    <button type="button" 
                            class="mt-3 inline-flex w-full justify-center rounded-xl bg-white dark:bg-gray-700 px-5 py-3 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all active:scale-95 sm:mt-0 sm:w-auto"
                            @click="cancel()">
                        <span x-text="config.cancelText"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmManager() {
    return {
        isOpen: false,
        config: {
            title: 'Konfirmasi Tindakan',
            message: 'Apakah Anda yakin ingin melanjutkan?',
            confirmText: 'Ya, Lanjutkan',
            cancelText: 'Batal',
            type: 'danger'
        },
        callback: null,

        open(data) {
            this.config = {
                title: data.title || (data.type === 'danger' ? 'Konfirmasi Hapus' : 'Konfirmasi Tindakan'),
                message: data.message || 'Apakah Anda yakin?',
                confirmText: data.confirmText || 'Ya, Lanjutkan',
                cancelText: data.cancelText || 'Batal',
                type: data.type || 'danger'
            };
            this.callback = data.callback || null;
            this.isOpen = true;
        },

        confirmAction() {
            if (this.callback && typeof this.callback === 'function') {
                this.callback();
            }
            this.isOpen = false;
        },

        cancel() {
            this.isOpen = false;
        }
    }
}

// Global helper
window.showConfirm = function(message, callback, type = 'danger', title = null) {
    let data = {};
    if (typeof message === 'object') {
        data = message;
    } else {
        data = {
            message: message,
            callback: callback,
            type: type,
            title: title
        };
    }

    window.dispatchEvent(new CustomEvent('confirm', {
        detail: data
    }));
};
</script>
