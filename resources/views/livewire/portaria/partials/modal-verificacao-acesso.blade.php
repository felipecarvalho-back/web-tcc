@if ($showVerificationModal && $selectedRecord)
    <x-modal 
        title="Dupla Verificação de Acesso - Veículo Não Identificado"
        subtitle="Correção de Leitura do OCR ou Identificação por Código de Acesso"
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

        <!-- Orientações da Dupla Verificação -->
        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-950 text-xs flex items-start gap-2.5">
            <span class="material-symbols-outlined text-[20px] text-amber-700 shrink-0 mt-0.5">info</span>
            <div class="flex flex-col gap-0.5">
                <span class="font-bold">Placa não identificada ou não encontrada no banco de dados</span>
                <p class="text-[11.5px] leading-relaxed text-amber-900">
                    O porteiro pode <strong>corrigir a placa</strong> (caso o OCR tenha lido incorretamente) ou <strong>solicitar o código de acesso</strong> do motorista (caso seja um docente ou funcionário em carro de terceiro/amigo).
                </p>
            </div>
        </div>

        <!-- Opção 1: Correção da Placa lida pelo OCR -->
        <div class="flex flex-col gap-1.5 p-3.5 rounded-xl border border-surface-container bg-surface-container-lowest">
            <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-on-surface flex items-center gap-1.5" for="corrected-plate-input">
                    <span class="w-5 h-5 rounded-full bg-primary/10 text-primary text-[11px] font-bold flex items-center justify-center">1</span>
                    <span>Corrigir Placa do Veículo (Se houve falha no OCR)</span>
                </label>
                <span class="text-[11px] text-on-surface-variant font-mono">Original: {{ $selectedRecord['plate'] }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center mt-1">
                <input 
                    wire:model="correctedPlate"
                    id="corrected-plate-input"
                    type="text" 
                    placeholder="Ex: BRA-2E19" 
                    class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest focus:border-primary text-on-surface font-mono font-bold text-sm uppercase rounded-xl focus:outline-none tracking-wider text-center"
                />
                <p class="text-[11px] text-on-surface-variant">
                    Corrija caracteres ilegíveis devido a reflexo solar, sujeira ou ângulo da câmera.
                </p>
            </div>
        </div>

        <!-- Opção 2: Código de Acesso do Motorista -->
        <div class="flex flex-col gap-1.5 p-3.5 rounded-xl border border-surface-container bg-surface-container-lowest">
            <label class="text-xs font-bold text-on-surface flex items-center gap-1.5" for="driver-access-code-input">
                <span class="w-5 h-5 rounded-full bg-primary/10 text-primary text-[11px] font-bold flex items-center justify-center">2</span>
                <span>Código de Acesso do Motorista (Carro de Terceiro / Não Cadastrado)</span>
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center mt-1">
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-on-surface-variant">
                        <span class="material-symbols-outlined text-[18px] leading-none">badge</span>
                    </div>
                    <input 
                        wire:model="driverAccessCode"
                        id="driver-access-code-input"
                        type="text" 
                        placeholder="Ex: DOC-94281 ou ADM-10293" 
                        class="w-full h-11 pl-11 pr-3 bg-surface-container-low border border-surface-container-highest focus:border-primary text-on-surface font-mono font-bold text-sm uppercase rounded-xl focus:outline-none tracking-wider"
                    />
                </div>
                <p class="text-[11px] text-on-surface-variant">
                    Informe a matrícula funcional do professor/funcionário caso o veículo não pertença a ele.
                </p>
            </div>
        </div>

        <!-- Justificativa da Liberação -->
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold text-on-surface" for="verification-justification">
                Motivo / Justificativa da Liberação
            </label>
            <select 
                wire:model="justification"
                id="verification-justification"
                class="w-full h-10 px-3 bg-surface-container-lowest border border-surface-container-highest text-on-surface text-xs font-medium rounded-xl focus:outline-none focus:border-primary cursor-pointer"
            >
                <option value="Correção manual de leitura OCR">Correção manual de leitura OCR</option>
                <option value="Veículo de terceiro / Carro emprestado de amigo ou parente">Veículo de terceiro / Carro emprestado de amigo ou parente</option>
                <option value="Veículo novo ainda não cadastrado no sistema">Veículo novo ainda não cadastrado no sistema</option>
                <option value="Veículo reserva / Carro alugado / Oficina mecânica">Veículo reserva / Carro alugado / Oficina mecânica</option>
                <option value="Outros motivos operacionais da guarita">Outros motivos operacionais da guarita</option>
            </select>
        </div>

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
