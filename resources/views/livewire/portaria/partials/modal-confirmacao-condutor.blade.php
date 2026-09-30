@if ($showDriverConfirmationModal && $selectedRecord)
    <x-modal 
        title="Confirmação de Condutor - Veículo Cadastrado"
        subtitle="Verificação de Identidade do Motorista na Cancela"
        icon="how_to_reg"
        onClose="closeDriverConfirmationModal"
        maxWidth="lg"
    >
        <!-- Resumo do Veículo e Condutor Cadastrado -->
        <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Veículo Identificado</span>
                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                    Cadastro Ativo
                </span>
            </div>

            <div class="flex items-center justify-between gap-3">
                <x-mercosul-plate :plate="$selectedRecord['plate']" size="sm" />
                <div class="text-right">
                    <span class="text-xs font-bold text-on-surface block">{{ $selectedRecord['driver_name'] }}</span>
                    <span class="text-[11px] text-on-surface-variant block">{{ $selectedRecord['category_label'] }}</span>
                </div>
            </div>
        </div>

        <!-- Orientações -->
        <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-950 text-xs flex items-start gap-2.5">
            <span class="material-symbols-outlined text-[20px] text-blue-700 shrink-0 mt-0.5">shield</span>
            <div class="flex flex-col gap-0.5">
                <span class="font-bold">Confirmação de Segurança da Cancela</span>
                <p class="text-[11.5px] leading-relaxed text-blue-800">
                    Solicite o <strong>código de acesso funcional</strong> do motorista para validar se a pessoa que está ao volante é realmente o condutor titular registrado no banco de dados.
                </p>
            </div>
        </div>

        <!-- Campo de Código de Acesso do Motorista -->
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold text-on-surface" for="confirm-driver-code-input">
                Código de Acesso do Condutor *
            </label>
            <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">pin</span>
                <input 
                    wire:model="confirmDriverCode"
                    id="confirm-driver-code-input"
                    type="text" 
                    placeholder="Ex: DOC-88312 ou ADM-10293" 
                    class="w-full h-11 pl-10 pr-3 bg-surface-container-lowest border border-surface-container-highest focus:border-primary text-on-surface font-mono font-bold text-sm uppercase rounded-xl focus:outline-none tracking-wider"
                    autofocus
                />
            </div>
            <span class="text-[11px] text-on-surface-variant">
                Peça ao condutor para ditar ou apresentar a matrícula/código de acesso.
            </span>
        </div>

        <x-slot:footer>
            <button 
                type="button" 
                wire:click="closeDriverConfirmationModal"
                class="px-4 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold transition-colors cursor-pointer"
            >
                Cancelar
            </button>
            <button 
                type="button" 
                wire:click="confirmDriver"
                class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                <span>Confirmar e Liberar Cancela</span>
            </button>
        </x-slot:footer>
    </x-modal>
@endif
