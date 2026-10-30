@if ($showBlockModal && $selectedRecord)
    <x-modal
        title="Bloquear Acesso do Veículo"
        subtitle="Registrar recusa ou impedimento de entrada na cancela"
        icon="block"
        onClose="closeBlockModal"
        maxWidth="lg"
    >
        <!-- Resumo do Veículo Consultado -->
        <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-14 h-11 rounded-lg overflow-hidden bg-black shrink-0 border border-surface-container">
                    <img src="{{ $selectedRecord['image_url'] }}" alt="Foto do veículo" class="w-full h-full object-cover">
                </div>
                <div class="flex flex-col">
                    <span class="text-[11px] text-on-surface-variant font-semibold uppercase">Veículo na Cancela</span>
                    <div class="flex items-center gap-2 mt-0.5">
                        <x-mercosul-plate :plate="$selectedRecord['plate']" size="sm" />
                    </div>
                </div>
            </div>

            <div class="text-right">
                <span class="text-[11px] text-on-surface-variant font-semibold">Horário da Chegada</span>
                <p class="text-xs font-bold font-mono text-on-surface">{{ $selectedRecord['registered_at'] }}</p>
                <span class="text-[10px] text-on-surface-variant">{{ $selectedRecord['time_ago'] }}</span>
            </div>
        </div>

        <!-- Alerta Operacional -->
        <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-950 text-xs flex items-start gap-2.5">
            <span class="material-symbols-outlined text-[20px] text-red-700 shrink-0 mt-0.5">warning</span>
            <div class="flex flex-col gap-0.5">
                <span class="font-bold text-red-900">Atenção: A cancela permanecerá fechada</span>
                <p class="text-[11.5px] leading-relaxed text-red-800">
                    O acesso será marcado como <strong>Bloqueado</strong> no sistema com o operador responsável, a foto e o motivo registrado para fins de auditoria e segurança patrimonial da FATEC.
                </p>
            </div>
        </div>

        <!-- Motivo do Bloqueio -->
        <div class="flex flex-col gap-2.5">
            <label class="text-xs font-bold text-on-surface">
                Motivo do Bloqueio / Recusa de Entrada *
            </label>

            <div class="flex flex-col gap-2">
                @php
                    $motivos = [
                        'Condutor não possui código de acesso',
                        'Veículo não cadastrado e sem autorização',
                        'Condutor recusou identificação na portaria',
                        'Outro motivo',
                    ];
                @endphp

                @foreach ($motivos as $m)
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all {{ $blockReason === $m ? 'border-red-600 bg-red-50/50' : 'border-surface-container bg-surface-container-lowest hover:bg-surface-container-low' }}">
                        <input
                            type="radio"
                            wire:model.live="blockReason"
                            value="{{ $m }}"
                            class="text-red-600 focus:ring-red-600 h-4 w-4"
                        >
                        <span class="text-xs font-semibold text-on-surface">{{ $m }}</span>
                    </label>
                @endforeach
            </div>

            <!-- Justificativa adicional se for 'Outro motivo' ou detalhamento opcional -->
            <div class="flex flex-col gap-1 mt-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider" for="block-custom-justification">
                    {{ $blockReason === 'Outro motivo' ? 'Especifique o motivo detalhado *' : 'Observações adicionais do operador (opcional)' }}
                </label>
                <textarea
                    wire:model="blockCustomJustification"
                    id="block-custom-justification"
                    rows="2"
                    placeholder="{{ $blockReason === 'Outro motivo' ? 'Descreva o motivo do bloqueio do acesso...' : 'Ex: Condutor orientado a procurar a secretaria para cadastramento...' }}"
                    class="w-full p-2.5 bg-surface-container-low border border-surface-container-highest focus:border-red-600 text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-red-600/20"
                ></textarea>
            </div>
        </div>

        <x-slot:footer>
            <button
                type="button"
                wire:click="closeBlockModal"
                class="px-4 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold transition-colors cursor-pointer"
            >
                Cancelar
            </button>
            <button
                type="button"
                wire:click="confirmBlock"
                wire:loading.attr="disabled"
                class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">block</span>
                <span wire:loading.remove wire:target="confirmBlock">Confirmar Bloqueio</span>
                <span wire:loading wire:target="confirmBlock">Bloqueando...</span>
            </button>
        </x-slot:footer>
    </x-modal>
@endif
