<!-- Seção de Caronas / Múltiplos Ocupantes no Veículo (Até 4 caronas adicionais, totalizando até 5 ocupantes) -->
<div class="flex flex-col gap-2 p-3.5 rounded-xl border border-dashed border-surface-container-highest bg-surface-container-lowest/60">
    <div class="flex items-center justify-between">
        <label class="text-xs font-bold text-on-surface flex items-center gap-1.5" for="passenger-code-input">
            <span class="material-symbols-outlined text-primary text-[18px]">group_add</span>
            <span>Carona Solidária / Docentes e Funcionários no Veículo</span>
        </label>
        <span class="text-[11px] font-semibold {{ count($passengers) >= 4 ? 'text-amber-700 font-bold' : 'text-on-surface-variant' }}">
            {{ count($passengers) }}/4 carona(s) adicionada(s)
        </span>
    </div>
    <p class="text-[11px] text-on-surface-variant">
        Adicione a matrícula/código funcional de outros professores ou colaboradores no carro para gerar o registro de presença de cada um.
    </p>

    <!-- Formulário de Adição Rápida de Carona -->
    @if (count($passengers) < 4)
        <div class="flex items-center gap-2 mt-1">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[18px]">badge</span>
                <input 
                    wire:model="newPassengerCode"
                    wire:keydown.enter.prevent="addPassenger"
                    id="passenger-code-input"
                    type="text" 
                    placeholder="Código do Carona (ex: DOC-74192)" 
                    class="w-full h-9 pl-9 pr-3 bg-surface-container-low border border-surface-container-highest focus:border-primary text-on-surface font-mono font-bold text-xs uppercase rounded-lg focus:outline-none tracking-wider"
                />
            </div>
            <button 
                type="button" 
                wire:click="addPassenger"
                class="h-9 px-3 rounded-lg bg-surface-container-high hover:bg-primary hover:text-white text-on-surface text-xs font-bold flex items-center gap-1 transition-colors cursor-pointer shrink-0"
                title="Adicionar ocupante à lista"
            >
                <span class="material-symbols-outlined text-[16px]">add</span>
                <span>Adicionar</span>
            </button>
        </div>
    @else
        <div class="p-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-[11px] text-center font-medium">
            Capacidade máxima de caronas atingida para este veículo (5 pessoas no total).
        </div>
    @endif

    <!-- Lista de Passageiros Adicionados -->
    @if (count($passengers) > 0)
        <div class="flex flex-wrap gap-2 mt-1.5 pt-2 border-t border-surface-container">
            @foreach ($passengers as $index => $passenger)
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-primary/10 text-primary border border-primary/20 text-xs font-bold">
                    <span class="material-symbols-outlined text-[14px]">person</span>
                    <span class="font-mono">Carona: {{ $passenger }}</span>
                    <button 
                        type="button" 
                        wire:click="removePassenger({{ $index }})" 
                        class="hover:text-red-600 transition-colors cursor-pointer ml-1 text-on-surface-variant"
                        title="Remover carona"
                    >
                        <span class="material-symbols-outlined text-[14px]">close</span>
                    </button>
                </div>
            @endforeach
        </div>
    @endif
</div>
