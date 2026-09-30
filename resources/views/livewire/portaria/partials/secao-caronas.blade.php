<!-- Seção de Caronas / Múltiplos Ocupantes no Veículo (Até 4 caronas adicionais, totalizando até 5 ocupantes) -->
<div class="flex flex-col gap-2 p-3.5 rounded-xl border border-dashed border-surface-container-highest bg-surface-container-lowest/60">
    <div class="flex items-center justify-between">
        <label class="text-xs font-bold text-on-surface flex items-center gap-1.5" for="passenger-code-input">
            <span class="material-symbols-outlined text-primary text-[19px] leading-none">group_add</span>
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
            <div class="relative flex-1 flex items-center">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant">
                    <span class="material-symbols-outlined text-[18px] leading-none">badge</span>
                </div>
                <input 
                    wire:model="newPassengerCode"
                    wire:keydown.enter.prevent="addPassenger"
                    id="passenger-code-input"
                    type="text" 
                    placeholder="Código do Carona (ex: DOC-74192)" 
                    class="w-full h-10 pl-10 pr-3 bg-surface-container-low border border-surface-container-highest focus:border-primary text-on-surface font-mono font-bold text-xs uppercase rounded-lg focus:outline-none tracking-wider"
                />
            </div>
            <button 
                type="button" 
                wire:click="addPassenger"
                class="h-10 px-3.5 rounded-lg bg-surface-container-high hover:bg-primary hover:text-white text-on-surface text-xs font-bold inline-flex items-center justify-center gap-1.5 transition-colors cursor-pointer shrink-0"
                title="Adicionar ocupante à lista"
            >
                <span class="material-symbols-outlined text-[18px] leading-none">add</span>
                <span>Adicionar</span>
            </button>
        </div>
    @else
        <div class="p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-[11px] text-center font-medium">
            Capacidade máxima de caronas atingida para este veículo (5 pessoas no total).
        </div>
    @endif

    <!-- Lista de Passageiros Adicionados -->
    @if (count($passengers) > 0)
        <div class="flex flex-wrap gap-2 mt-1.5 pt-2 border-t border-surface-container">
            @foreach ($passengers as $index => $passenger)
                <div wire:key="passenger-{{ $index }}-{{ $passenger }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-primary/10 text-primary border border-primary/20 text-xs font-bold">
                    <span class="material-symbols-outlined text-[15px] leading-none">person</span>
                    <span class="font-mono">Carona: {{ $passenger }}</span>
                    <button 
                        type="button" 
                        wire:click="removePassenger({{ $index }})" 
                        class="p-0.5 rounded hover:bg-red-100 hover:text-red-700 transition-colors cursor-pointer ml-0.5 text-on-surface-variant flex items-center justify-center"
                        title="Remover carona"
                    >
                        <span class="material-symbols-outlined text-[15px] leading-none">close</span>
                    </button>
                </div>
            @endforeach
        </div>
    @endif
</div>
