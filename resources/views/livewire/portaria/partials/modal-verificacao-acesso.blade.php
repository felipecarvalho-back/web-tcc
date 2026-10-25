@if ($showVerificationModal && $selectedRecord)
    <x-modal
        title="Verificação de Acesso - Placa Não Identificada no Banco"
        subtitle="Corrija a placa com código de acesso ou valide carro de terceiro"
        icon="fact_check"
        onClose="closeVerificationModal"
        maxWidth="2xl"
    >
        <!-- Resumo da Captura da Cancela -->
        <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-16 h-12 rounded-lg overflow-hidden bg-black shrink-0 border border-surface-container">
                    <img src="{{ $selectedRecord['image_url'] }}" alt="Captura da Cancela" class="w-full h-full object-cover">
                </div>
                <div class="flex flex-col">
                    <span class="text-[11px] text-on-surface-variant font-semibold uppercase">Leitura da Cancela 01</span>
                    <div class="flex items-center gap-2 mt-0.5">
                        <x-mercosul-plate :plate="$selectedRecord['plate']" size="sm" />
                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[10px] font-bold">
                            {{ $selectedRecord['status_label'] }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="text-right sm:border-l sm:border-surface-container sm:pl-4">
                <span class="text-[11px] text-on-surface-variant font-semibold">Horário da Chegada</span>
                <p class="text-xs font-bold font-mono text-on-surface">{{ $selectedRecord['registered_at'] }} ({{ $selectedRecord['time_ago'] }})</p>
            </div>
        </div>

        <!-- Seletor do Tipo de Ocorrência na Guarita -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button
                type="button"
                wire:click="$set('verificationMode', 'corrigir_placa')"
                class="p-3.5 rounded-xl border text-left transition-all cursor-pointer flex items-start gap-3 {{ $verificationMode === 'corrigir_placa' ? 'border-primary bg-primary/5 ring-2 ring-primary/20' : 'border-surface-container bg-surface-container-lowest hover:bg-surface-container-low' }}"
            >
                <span class="material-symbols-outlined text-[22px] {{ $verificationMode === 'corrigir_placa' ? 'text-primary' : 'text-on-surface-variant' }}">edit_square</span>
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-bold text-on-surface">1. Corrigir Placa + Código</span>
                    <span class="text-[11px] text-on-surface-variant leading-snug">
                        Falha no OCR (Professor, Funcionário ou Prestador com veículo cadastrado).
                    </span>
                </div>
            </button>

            <button
                type="button"
                wire:click="$set('verificationMode', 'carro_terceiro')"
                class="p-3.5 rounded-xl border text-left transition-all cursor-pointer flex items-start gap-3 {{ $verificationMode === 'carro_terceiro' ? 'border-primary bg-primary/5 ring-2 ring-primary/20' : 'border-surface-container bg-surface-container-lowest hover:bg-surface-container-low' }}"
            >
                <span class="material-symbols-outlined text-[22px] {{ $verificationMode === 'carro_terceiro' ? 'text-primary' : 'text-on-surface-variant' }}">badge</span>
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-bold text-on-surface">2. Carro de Terceiro</span>
                    <span class="text-[11px] text-on-surface-variant leading-snug">
                        Apenas código de acesso (Exclusivo para Professor e Funcionário).
                    </span>
                </div>
            </button>
        </div>

        @if ($verificationMode === 'corrigir_placa')
            <!-- MODO 1: CORRIGIR PLACA + CÓDIGO DE ACESSO (TODOS OS VÍNCULOS) -->
            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-950 text-xs flex items-start gap-2.5">
                <span class="material-symbols-outlined text-[20px] text-amber-700 shrink-0 mt-0.5">info</span>
                <div class="flex flex-col gap-0.5">
                    <span class="font-bold">Correção de Placa + Código de Acesso (Todos os Casos)</span>
                    <p class="text-[11.5px] leading-relaxed text-amber-900">
                        Informe a <strong>placa correta do veículo</strong> e o <strong>código de acesso</strong> do condutor (Professor, Funcionário ou Prestador de Serviço) para validar no banco e liberar a cancela.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl border border-surface-container bg-surface-container-lowest">
                <!-- Placa Corrigida -->
                <div class="flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-on-surface" for="corrected-plate-input">
                            Placa Corrigida do Veículo *
                        </label>
                        <span class="text-[10px] text-on-surface-variant font-mono">Lida: {{ $selectedRecord['plate'] }}</span>
                    </div>
                    <input
                        wire:model="correctedPlate"
                        id="corrected-plate-input"
                        type="text"
                        x-mask="***-****"
                        maxlength="8"
                        placeholder="Ex: ABC-1234"
                        class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest focus:border-primary text-on-surface font-mono font-bold text-sm uppercase rounded-xl focus:outline-none tracking-wider text-center"
                    />
                    @error('correctedPlate')
                        <span class="text-error text-[11px] font-semibold">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Código de Acesso -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface" for="driver-access-code-input">
                        Código de Acesso do Condutor *
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-on-surface-variant">
                            <span class="material-symbols-outlined text-[18px] leading-none">pin</span>
                        </div>
                        <input
                            wire:model="driverAccessCode"
                            wire:keydown.enter.prevent="confirmVerification"
                            id="driver-access-code-input"
                            type="text"
                            placeholder="Ex: DOC-88312, ADM-101 ou PST-5512"
                            class="w-full h-11 pl-11 pr-3 bg-surface-container-low border border-surface-container-highest focus:border-primary text-on-surface font-mono font-bold text-sm uppercase rounded-xl focus:outline-none tracking-wider"
                        />
                    </div>
                    @error('driverAccessCode')
                        <span class="text-error text-[11px] font-semibold">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        @else
            <!-- MODO 2: CARRO DE TERCEIROS (APENAS PROFESSOR E FUNCIONÁRIO) -->
            <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-950 text-xs flex items-start gap-2.5">
                <span class="material-symbols-outlined text-[20px] text-blue-700 shrink-0 mt-0.5">verified_user</span>
                <div class="flex flex-col gap-0.5">
                    <span class="font-bold">Acesso em Veículo de Terceiros (Exclusivo Professor e Funcionário)</span>
                    <p class="text-[11.5px] leading-relaxed text-blue-900">
                        Quando um <strong>Professor</strong> ou <strong>Funcionário</strong> estiver dirigindo um carro emprestado, alugado ou de familiar, basta informar o <strong>código de acesso funcional</strong> para liberar.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 {{ $selectedRecord['plate'] === 'SEM-PLACA' ? 'sm:grid-cols-2' : '' }} gap-4 p-4 rounded-xl border border-surface-container bg-surface-container-lowest">
                <!-- Código de Acesso (Obrigatório) -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface" for="third-party-code-input">
                        Código de Acesso (Professor ou Funcionário) *
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-on-surface-variant">
                            <span class="material-symbols-outlined text-[18px] leading-none">badge</span>
                        </div>
                        <input
                            wire:model="driverAccessCode"
                            wire:keydown.enter.prevent="confirmVerification"
                            id="third-party-code-input"
                            type="text"
                            placeholder="Ex: DOC-88312 ou ADM-10293"
                            class="w-full h-11 pl-11 pr-3 bg-surface-container-low border border-surface-container-highest focus:border-primary text-on-surface font-mono font-bold text-sm uppercase rounded-xl focus:outline-none tracking-wider"
                            autofocus
                        />
                    </div>
                    @error('driverAccessCode')
                        <span class="text-error text-[11px] font-semibold">{{ $message }}</span>
                    @enderror
                </div>

                @if ($selectedRecord['plate'] === 'SEM-PLACA')
                    <!-- Caso o OCR não tenha lido a placa do carro de terceiro -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-on-surface" for="third-party-plate-input">
                            Placa do Veículo (Opcional)
                        </label>
                        <input
                            wire:model="correctedPlate"
                            id="third-party-plate-input"
                            type="text"
                            x-mask="***-****"
                            maxlength="8"
                            placeholder="Ex: ABC-1234"
                            class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest focus:border-primary text-on-surface font-mono font-bold text-sm uppercase rounded-xl focus:outline-none tracking-wider text-center"
                        />
                    </div>
                @endif
            </div>
        @endif

        <!-- Seção de Caronas / Múltiplos Ocupantes (Opcional - Até 4 caronas) -->
        @include('livewire.portaria.partials.secao-caronas')

        <x-slot:footer>
            <button
                type="button"
                wire:click="closeVerificationModal"
                class="px-4 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold transition-colors cursor-pointer"
            >
                Cancelar
            </button>
            <button
                type="button"
                wire:click="confirmVerification"
                wire:loading.attr="disabled"
                class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container disabled:opacity-50 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">garage</span>
                <span>Validar e Liberar Cancela</span>
            </button>
        </x-slot:footer>
    </x-modal>
@endif
