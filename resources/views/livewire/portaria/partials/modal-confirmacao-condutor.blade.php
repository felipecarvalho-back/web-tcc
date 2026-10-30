@if ($showDriverConfirmationModal && $selectedRecord)
    <x-modal
        title="Confirmação de Condutor - Veículo Cadastrado"
        subtitle="Placa identificada no banco de dados — valide o código para liberar"
        icon="how_to_reg"
        onClose="closeDriverConfirmationModal"
        maxWidth="lg"
    >
        <!-- Resumo do Veículo e Condutor Cadastrado -->
        <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Veículo Identificado no Banco</span>
                <div class="flex items-center gap-1.5">
                    @if ($selectedRecord['is_driver_expired'] ?? false)
                        <span class="px-2 py-0.5 rounded-full bg-error-container text-error text-[10px] font-bold">
                            Autorização Vencida
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                            Cadastro Ativo • {{ $selectedRecord['driver_validity'] ?? 'Permanente' }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                <x-mercosul-plate :plate="$selectedRecord['plate']" size="sm" />
                <div class="text-right">
                    <span class="text-xs font-bold text-on-surface block">{{ $selectedRecord['driver_name'] }}</span>
                    <span class="text-[11px] text-on-surface-variant block">{{ $selectedRecord['category_label'] }}</span>
                </div>
            </div>
        </div>

        @if ($selectedRecord['is_driver_expired'] ?? false)
            <div class="p-3.5 rounded-xl bg-error-container text-error text-xs flex items-start gap-2.5">
                <span class="material-symbols-outlined text-[20px] shrink-0 mt-0.5">event_busy</span>
                <div class="flex flex-col gap-0.5">
                    <span class="font-bold text-error">Atenção: Prazo de Autorização Vencido!</span>
                    <p class="text-[11.5px] leading-relaxed text-error/90">
                        A autorização deste condutor venceu em <strong>{{ $selectedRecord['driver_validity'] ?? '' }}</strong>. O acesso pela cancela requer renovação cadastral junto à administração.
                    </p>
                </div>
            </div>
        @endif

        <!-- Orientações -->
        <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-950 text-xs flex items-start gap-2.5">
            <span class="material-symbols-outlined text-[20px] text-blue-700 shrink-0 mt-0.5">shield</span>
            <div class="flex flex-col gap-0.5">
                <span class="font-bold">Validação de Código na Cancela</span>
                <p class="text-[11.5px] leading-relaxed text-blue-800">
                    A placa foi reconhecida no banco de dados. Solicite apenas o <strong>código de acesso</strong> (Professor, Funcionário ou Prestador de Serviço) para confirmar o motorista e liberar o veículo.
                </p>
            </div>
        </div>

        <!-- Campo de Código de Acesso do Motorista -->
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold text-on-surface" for="confirm-driver-code-input">
                Código de Acesso do Condutor *
            </label>
            <div class="relative flex items-center">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-on-surface-variant">
                    <span class="material-symbols-outlined text-[20px] leading-none">pin</span>
                </div>
                <input
                    wire:model="confirmDriverCode"
                    wire:keydown.enter.prevent="confirmDriver"
                    id="confirm-driver-code-input"
                    type="text"
                    x-mask="***-*****"
                    maxlength="9"
                    placeholder="Ex: DOC-88312, ADM-10293 ou PST-5512"
                    class="w-full h-11 pl-11 pr-3 bg-surface-container-lowest border border-surface-container-highest focus:border-primary text-on-surface font-mono font-bold text-sm uppercase rounded-xl focus:outline-none tracking-wider"
                    autofocus
                />
            </div>
            @error('confirmDriverCode')
                <span class="text-error text-[11px] font-semibold">{{ $message }}</span>
            @enderror
            <span class="text-[11px] text-on-surface-variant">
                Peça ao condutor para informar o código funcional ou matrícula cadastrada.
            </span>
        </div>

        <!-- Seção de Caronas / Múltiplos Ocupantes (Apenas Professor e Funcionário) -->
        @if (($selectedRecord['category'] ?? '') !== 'prestador')
            @include('livewire.portaria.partials.secao-caronas')
        @endif

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
                wire:loading.attr="disabled"
                class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container disabled:opacity-50 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                <span>Confirmar e Liberar Cancela</span>
            </button>
        </x-slot:footer>
    </x-modal>
@endif
