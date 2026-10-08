@props([
    'message' => null,
    'type' => 'success',
])

@if ($message)
    <div 
        x-data="{ 
            show: true,
            timer: null,
            init() {
                this.timer = setTimeout(() => {
                    this.close();
                }, 4000);
            },
            close() {
                this.show = false;
                setTimeout(() => {
                    if (typeof $wire !== 'undefined' && $wire.clearToast) {
                        $wire.clearToast();
                    }
                }, 300);
            }
        }" 
        x-show="show" 
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-[-12px] sm:translate-y-0 sm:translate-x-12 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-[-12px] sm:translate-y-0 sm:translate-x-12 scale-95"
        class="fixed top-5 right-5 z-[999] max-w-sm w-full sm:w-auto flex items-center gap-3 px-4 py-3 rounded-2xl shadow-xl border text-xs sm:text-sm font-semibold backdrop-blur-md {{ 
            match ($type) {
                'success' => 'bg-emerald-600/95 text-white border-emerald-500 shadow-emerald-950/20',
                'warning' => 'bg-amber-600/95 text-white border-amber-500 shadow-amber-950/20',
                'error'   => 'bg-red-600/95 text-white border-red-500 shadow-red-950/20',
                'info'    => 'bg-blue-600/95 text-white border-blue-500 shadow-blue-950/20',
                default   => 'bg-blue-600/95 text-white border-blue-500 shadow-blue-950/20',
            }
        }}"
    >
        <span class="material-symbols-outlined text-[20px] shrink-0">
            {{ match ($type) {
                'success' => 'check_circle',
                'warning' => 'warning',
                'error'   => 'error',
                default   => 'info',
            } }}
        </span>

        <span class="flex-1 leading-snug">{{ $message }}</span>

        <button 
            type="button" 
            @click="close()" 
            class="p-1 rounded-lg hover:bg-white/20 text-white/80 hover:text-white transition-colors cursor-pointer shrink-0"
            title="Fechar notificação"
        >
            <span class="material-symbols-outlined text-[16px] block">close</span>
        </button>
    </div>
@endif
