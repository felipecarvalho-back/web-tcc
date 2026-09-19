@props([
    'message' => null,
    'type' => 'success',
])

@if ($message)
    <div 
        x-data="{ show: true }" 
        x-show="show" 
        x-init="setTimeout(() => { show = false; $wire.clearToast(); }, 4000)"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-semibold transition-all {{ $type === 'success' ? 'bg-emerald-600 text-white border-emerald-500' : ($type === 'warning' ? 'bg-amber-600 text-white border-amber-500' : 'bg-primary text-white border-primary-container') }}"
    >
        <span class="material-symbols-outlined text-[20px]">
            {{ $type === 'success' ? 'check_circle' : ($type === 'warning' ? 'warning' : 'info') }}
        </span>
        <span>{{ $message }}</span>
        <button type="button" @click="show = false; $wire.clearToast()" class="ml-2 text-white/80 hover:text-white cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
    </div>
@endif
