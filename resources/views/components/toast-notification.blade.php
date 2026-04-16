<!-- Center-Expand Toast Notification Component -->
<div x-data="toastManager()" @notify.window="show($event.detail)"
    class="fixed top-6 left-0 right-0 z-[9999] flex flex-col items-center gap-3 pointer-events-none">

    <template x-for="(toast, index) in toasts" :key="toast.id">
        <div class="pointer-events-auto" x-show="toast.phase !== 'hidden'"
            x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-50">

            <div class="relative flex items-center overflow-hidden rounded-2xl shadow-2xl border backdrop-blur-md"
                :class="{
                     'bg-green-50/95 dark:bg-green-950/90 border-green-200/60 dark:border-green-800/60 shadow-green-500/10': toast.type === 'success',
                     'bg-red-50/95 dark:bg-red-950/90 border-red-200/60 dark:border-red-800/60 shadow-red-500/10': toast.type === 'error',
                     'bg-amber-50/95 dark:bg-amber-950/90 border-amber-200/60 dark:border-amber-800/60 shadow-amber-500/10': toast.type === 'warning',
                     'bg-blue-50/95 dark:bg-blue-950/90 border-blue-200/60 dark:border-blue-800/60 shadow-blue-500/10': toast.type === 'info'
                 }" :style="['expand', 'visible', 'collapse'].includes(toast.phase) 
                            ? 'max-width: 480px; height: 56px;' 
                            : 'max-width: 56px; height: 56px;'"
                style="transition: width 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), max-width 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), height 0.3s ease, transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease;">
                <!-- Icon Circle -->
                <div class="flex-shrink-0 flex items-center justify-center transition-all duration-500 ease-out"
                    :class="toast.phase === 'icon' || toast.phase === 'closing' ? 'w-14 h-14' : 'w-14 h-14 ml-0'"
                    :style="toast.phase === 'icon' || toast.phase === 'closing' 
                         ? 'transform: scale(1); opacity: 1;' 
                         : 'transform: scale(1); opacity: 1;'">

                    <!-- Success Icon -->
                    <template x-if="toast.type === 'success'">
                        <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center"
                            :class="toast.showIcon ? 'animate-icon-pop' : 'scale-0'">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"
                                    :class="toast.showIcon ? 'animate-check-draw' : ''"
                                    style="stroke-dasharray: 24; stroke-dashoffset: 24;" />
                            </svg>
                        </div>
                    </template>

                    <!-- Error Icon -->
                    <template x-if="toast.type === 'error'">
                        <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center"
                            :class="toast.showIcon ? 'animate-icon-pop' : 'scale-0'">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                    </template>

                    <!-- Warning Icon -->
                    <template x-if="toast.type === 'warning'">
                        <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center"
                            :class="toast.showIcon ? 'animate-icon-pop' : 'scale-0'">
                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </template>

                    <!-- Info Icon -->
                    <template x-if="toast.type === 'info'">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center"
                            :class="toast.showIcon ? 'animate-icon-pop' : 'scale-0'">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </template>
                </div>

                <!-- Message Content (appears during expand phase) -->
                <div class="overflow-hidden transition-all duration-500 ease-out" :style="['expand','visible'].includes(toast.phase)
                            ? 'max-width: 420px; opacity: 1; padding: 12px 20px 12px 0;'
                            : toast.phase === 'collapse'
                                ? 'max-width: 0px; opacity: 0; padding: 0;'
                                : 'max-width: 0px; opacity: 0; padding: 0;'">
                    <div class="whitespace-normal pr-2 min-w-[160px] max-w-[340px]">
                        <p class="text-sm font-bold leading-tight" :class="{
                               'text-green-800 dark:text-green-200': toast.type === 'success',
                               'text-red-800 dark:text-red-200': toast.type === 'error',
                               'text-amber-800 dark:text-amber-200': toast.type === 'warning',
                               'text-blue-800 dark:text-blue-200': toast.type === 'info'
                           }" x-text="toast.title"></p>
                        <p class="text-xs mt-0.5 leading-tight" :class="{
                               'text-green-700/80 dark:text-green-300/80': toast.type === 'success',
                               'text-red-700/80 dark:text-red-300/80': toast.type === 'error',
                               'text-amber-700/80 dark:text-amber-300/80': toast.type === 'warning',
                               'text-blue-700/80 dark:text-blue-300/80': toast.type === 'info'
                           }" x-text="toast.message" x-show="toast.message"></p>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<style>
    @keyframes iconPop {
        0% {
            transform: scale(0) rotate(-45deg);
            opacity: 0;
        }

        50% {
            transform: scale(1.2) rotate(0deg);
            opacity: 1;
        }

        100% {
            transform: scale(1) rotate(0deg);
            opacity: 1;
        }
    }

    @keyframes checkDraw {
        0% {
            stroke-dashoffset: 24;
        }

        100% {
            stroke-dashoffset: 0;
        }
    }

    .animate-icon-pop {
        animation: iconPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }

    .animate-check-draw {
        animation: checkDraw 0.4s cubic-bezier(0.65, 0, 0.35, 1) 0.2s forwards;
    }
</style>

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
                    title: data.title || this.getDefaultTitle(data.type || 'info'),
                    message: data.message || '',
                    phase: 'hidden',
                    showIcon: false,
                    duration: data.duration || 3000
                };

                this.toasts.push(toast);

                // Phase 1: Show icon (scale up from center)
                setTimeout(() => {
                    const idx = this.toasts.findIndex(t => t.id === id);
                    if (idx !== -1) {
                        this.toasts[idx].phase = 'icon';
                        this.toasts[idx].showIcon = true;
                    }
                }, 10);

                // Phase 2: Expand to show message
                setTimeout(() => {
                    const idx = this.toasts.findIndex(t => t.id === id);
                    if (idx !== -1) {
                        this.toasts[idx].phase = 'expand';
                    }
                }, 500);

                // Phase 3: Mark as fully visible
                setTimeout(() => {
                    const idx = this.toasts.findIndex(t => t.id === id);
                    if (idx !== -1) {
                        this.toasts[idx].phase = 'visible';
                    }
                }, 1000);

                // Phase 4: Collapse (Shrink back to icon FIRST)
                setTimeout(() => {
                    const idx = this.toasts.findIndex(t => t.id === id);
                    if (idx !== -1) {
                        this.toasts[idx].phase = 'collapse';
                    }
                }, toast.duration + 1000);

                // Phase 5: Fade out dan hapus element (Memicu x-transition Alpine.js)
                setTimeout(() => {
                    const idx = this.toasts.findIndex(t => t.id === id);
                    if (idx !== -1) {
                        this.toasts[idx].phase = 'closing';
                    }
                }, toast.duration + 1400);

                setTimeout(() => {
                    const idx = this.toasts.findIndex(t => t.id === id);
                    if (idx !== -1) {
                        this.toasts[idx].phase = 'hidden';
                    }

                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 500);
                }, toast.duration + 1800);
            },

            remove(id) {
                const idx = this.toasts.findIndex(t => t.id === id);
                if (idx !== -1) {
                    // 1. Mulai animasi melipat teks
                    this.toasts[idx].phase = 'closing';

                    // 2. Tunggu 400ms sampai teks terlipat, lalu mulai animasi menghilang
                    setTimeout(() => {
                        const currentIdx = this.toasts.findIndex(t => t.id === id);
                        if (currentIdx !== -1) {
                            this.toasts[currentIdx].phase = 'hidden';

                            // 3. Hapus bersih setelah animasi selesai
                            setTimeout(() => {
                                this.toasts = this.toasts.filter(t => t.id !== id);
                            }, 500);
                        }
                    }, 600);
                }
            },

            getDefaultTitle(type) {
                const titles = {
                    success: 'Berhasil!',
                    error: 'Gagal!',
                    warning: 'Peringatan!',
                    info: 'Informasi'
                };
                return titles[type] || 'Notifikasi';
            }
        }
    }

    // Global helper — backward-compatible with existing showToast calls
    window.showToast = function (type, message, title = null, duration = 3000) {
        const detail = typeof message === 'object' ? message : {
            type: type,
            message: message,
            title: title,
            duration: duration
        };

        window.dispatchEvent(new CustomEvent('notify', {
            detail: detail
        }));
    };
</script>