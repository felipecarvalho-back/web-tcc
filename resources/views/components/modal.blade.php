@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'onClose' => null,
    'maxWidth' => '2xl',
])

@php
$maxWidthClass = match ($maxWidth) {
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '3xl' => 'max-w-3xl',
    '4xl' => 'max-w-4xl',
    default => 'max-w-2xl',
};
@endphp

<div 
    x-data="{
        closeModal() {
            document.body.classList.remove('overflow-hidden');
            @if ($onClose)
                $wire.{{ $onClose }}();
            @endif
        }
    }" 
    x-init="document.body.classList.add('overflow-hidden'); $cleanup(() => document.body.classList.remove('overflow-hidden'))"
    @keydown.escape.window="closeModal()"
    @click="closeModal()"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs overscroll-contain select-none md:select-auto"
>
    <div 
        @click.stop
        {{ $attributes->merge(['class' => "w-full {$maxWidthClass} bg-surface-container-lowest rounded-2xl shadow-2xl border border-surface-container overflow-hidden flex flex-col animate-in fade-in zoom-in duration-150 select-text"]) }}
    >
        <!-- Header -->
        <div class="px-6 py-4 bg-primary text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if ($icon)
                    <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center text-white shrink-0">
                        <span class="material-symbols-outlined text-[22px]">{{ $icon }}</span>
                    </div>
                @endif
                <div class="flex flex-col">
                    <h3 class="text-base font-bold text-white leading-tight">
                        {{ $title }}
                    </h3>
                    @if ($subtitle)
                        <span class="text-xs text-white/80">{{ $subtitle }}</span>
                    @endif
                </div>
            </div>
            @if ($onClose)
                <button 
                    type="button" 
                    @click="closeModal()"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-white/80 hover:text-white hover:bg-white/10 transition-colors cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            @endif
        </div>

        <!-- Body com contenção de rolagem para impedir movimento da barra lateral e do fundo -->
        <div class="p-6 flex flex-col gap-4 max-h-[80vh] overflow-y-auto overscroll-contain">
            {{ $slot }}
        </div>

        <!-- Footer -->
        @isset($footer)
            <div class="px-6 py-4 bg-surface-container-low border-t border-surface-container flex items-center justify-end gap-3">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
