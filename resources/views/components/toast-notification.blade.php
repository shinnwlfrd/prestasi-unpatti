<!-- Toast Notification Component -->
<div x-data="toastManager()" 
     @notify.window="show($event.detail)"
     class="fixed top-4 right-4 z-[9999] space-y-3 pointer-events-none">
    
    <template x-for="(toast, index) in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="pointer-events-auto w-96 max-w-full">
            
            <div class="rounded-xl shadow-2xl border overflow-hidden"
                 :class="{
                     'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700': toast.type === 'info',
                     'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-800': toast.type === 'success',
                     'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800': toast.type === 'error',
                     'bg-amber-50 dark:bg-amber-900/30 border-amber-200 dark:border-amber-800': toast.type === 'warning'
                 }">
                
                <!-- Progress Bar -->
                <div class="h-1 bg-gray-200 dark:bg-gray-700">
                    <div class="h-full transition-all duration-100 ease-linear"
                         :class="{
                             'bg-blue-500': toast.type === 'info',
                             'bg-green-500': toast.type === 'success',
                             'bg-red-500': toast.type === 'error',
                             'bg-amber-500': toast.type === 'warning'
                         }"
                         :style="`width: ${toast.progress}%`"></div>
                </div>
                
                <div class="p-4 flex items-start gap-3">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <template x-if="toast.type === 'success'">
                            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </template>
                        
                        <template x-if="toast.type === 'error'">
                            <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        </template>
                        
                        <template x-if="toast.type === 'warning'">
                            <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                        </template>
                        
                        <template x-if="toast.type === 'info'">
                            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold"
                           :class="{
                               'text-gray-900 dark:text-white': toast.type === 'info',
                               'text-green-800 dark:text-green-300': toast.type === 'success',
                               'text-red-800 dark:text-red-300': toast.type === 'error',
                               'text-amber-800 dark:text-amber-300': toast.type === 'warning'
                           }"
                           x-text="toast.title"></p>
                        <p class="mt-1 text-sm"
                           :class="{
                               'text-gray-600 dark:text-gray-400': toast.type === 'info',
                               'text-green-700 dark:text-green-400': toast.type === 'success',
                               'text-red-700 dark:text-red-400': toast.type === 'error',
                               'text-amber-700 dark:text-amber-400': toast.type === 'warning'
                           }"
                           x-text="toast.message"></p>
                    </div>
                    
                    <!-- Close Button -->
                    <button @click="remove(toast.id)" 
                            class="flex-shrink-0 rounded-lg p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            :class="{
                                'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300': toast.type === 'info',
                                'text-green-400 hover:text-green-600 dark:hover:text-green-300': toast.type === 'success',
                                'text-red-400 hover:text-red-600 dark:hover:text-red-300': toast.type === 'error',
                                'text-amber-400 hover:text-amber-600 dark:hover:text-amber-300': toast.type === 'warning'
                            }">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function toastManager() {
    return {
        toasts: [],
        nextId: 1,
        
        show(data) {
            const id = this.nextId++;
            const toast = {
                id: id,
                type: data.type || 'info',
                title: data.title || this.getDefaultTitle(data.type),
                message: data.message || '',
                visible: false,
                progress: 100,
                duration: data.duration || 5000
            };
            
            this.toasts.push(toast);
            
            // Show with slight delay for animation
            setTimeout(() => {
                const index = this.toasts.findIndex(t => t.id === id);
                if (index !== -1) {
                    this.toasts[index].visible = true;
                }
            }, 10);
            
            // Start progress bar
            this.startProgress(id, toast.duration);
            
            // Auto remove
            setTimeout(() => {
                this.remove(id);
            }, toast.duration);
        },
        
        startProgress(id, duration) {
            const interval = 50; // Update every 50ms
            const steps = duration / interval;
            let currentStep = 0;
            
            const timer = setInterval(() => {
                currentStep++;
                const index = this.toasts.findIndex(t => t.id === id);
                
                if (index === -1) {
                    clearInterval(timer);
                    return;
                }
                
                this.toasts[index].progress = 100 - (currentStep / steps * 100);
                
                if (currentStep >= steps) {
                    clearInterval(timer);
                }
            }, interval);
        },
        
        remove(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index !== -1) {
                this.toasts[index].visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        },
        
        getDefaultTitle(type) {
            const titles = {
                success: 'Berhasil!',
                error: 'Terjadi Kesalahan!',
                warning: 'Peringatan!',
                info: 'Informasi'
            };
            return titles[type] || 'Notifikasi';
        }
    }
}

// Helper function to show toast from anywhere
window.showToast = function(type, message, title = null, duration = 5000) {
    window.dispatchEvent(new CustomEvent('notify', {
        detail: { type, message, title, duration }
    }));
};
</script>
