@props([
    'plate' => '',
    'size' => 'normal',
])

<div {{ $attributes->merge(['class' => 'inline-flex flex-col rounded-md bg-white border border-gray-300 shadow-xs overflow-hidden text-center ' . ($size === 'sm' ? 'w-24' : 'w-28')]) }}>
    <div class="bg-blue-800 text-white text-[9px] font-bold py-0.5 tracking-wider uppercase flex items-center justify-between px-1.5">
        <span>Brasil</span>
        <span class="text-[7px]">BR</span>
    </div>
    <div class="text-sm font-extrabold tracking-widest text-gray-900 py-1 font-mono">
        {{ $plate }}
    </div>
</div>
