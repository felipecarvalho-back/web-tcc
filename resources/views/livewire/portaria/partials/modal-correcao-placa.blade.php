@if ($showCorrectionModal && $selectedRecord)
    <x-modal 
        title="Correção Manual de Placa - Registro #{{ $selectedRecord['id'] }}"
        subtitle="Intervenção Operacional da Guarita"
        icon="document_scanner"
        onClose="closeCorrectionModal"
        maxWidth="2xl"
    >
        <!-- Foto Capturada com Região OCR -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-surface-container-low p-4 rounded-xl border border-surface-container">
            <div class="flex flex-col gap-1.5">
                <span class="text-[11px] font-bold text-on-surface-variant uppercase">Captura da Câmera</span>
                <div class="relative h-32 rounded-lg overflow-hidden bg-black flex items-center justify-center">
                    <img src="{{ $selectedRecord['image_url'] }}" alt="Captura" class="w-full h-full object-cover">
                    <div class="absolute inset-x-6 inset-y-6 rounded border-2 border-dashed border-amber-400 pointer-events-none flex items-end justify-start p-1">
                        <span class="bg-amber-500 text-black font-extrabold text-[9px] px-1 rounded">Região OCR</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-bold text-on-surface-variant uppercase">Dados da Passagem</span>
                    <div class="mt-2 space-y-1.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-surface-container">
                            <span class="text-on-surface-variant">Horário:</span>
                            <span class="font-bold text-on-surface font-mono">{{ $selectedRecord['registered_at'] }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-surface-container">
                            <span class="text-on-surface-variant">Leitura Bruta:</span>
                            <span class="font-bold text-on-surface font-mono">{{ $selectedRecord['raw_ocr'] }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-on-surface-variant">Confiança:</span>
                            <span class="font-bold text-amber-600">{{ $selectedRecord['confidence'] }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campos de Correção -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-on-surface">
                    Placa Corrigida (Mercosul / Padrão)
                </label>
                <input 
                    wire:model="correctedPlate" 
                    type="text" 
                    class="w-full h-11 px-3 bg-surface-container-lowest border-2 border-primary/50 focus:border-primary text-on-surface font-mono font-extrabold text-base uppercase rounded-xl focus:outline-none tracking-widest text-center"
                    placeholder="Ex: BRA-2E19"
                />
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-on-surface">
                    Vincular Categoria
                </label>
                <select 
                    wire:model="correctionCategory"
                    class="w-full h-11 px-3 bg-surface-container-lowest border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary"
                >
                    <option value="professor">Professor (Docente FATEC)</option>
                    <option value="funcionario">Funcionário Administrativo</option>
                    <option value="prestador">Prestador de Serviço</option>
                    <option value="visitante">Visitante / Convidado</option>
                </select>
            </div>
        </div>

        <!-- Justificativa -->
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold text-on-surface">
                Justificativa da Correção
            </label>
            <select 
                wire:model="correctionJustification"
                class="w-full h-10 px-3 bg-surface-container-lowest border border-surface-container-highest text-on-surface text-xs font-medium rounded-xl focus:outline-none focus:border-primary"
            >
                <option value="Reflexo solar sobre o caractere da placa">Reflexo solar sobre o caractere da placa</option>
                <option value="Caractere 8 confundido com B ou 0 com O">Caractere 8 confundido com B ou 0 com O</option>
                <option value="Placa física suja / desgastada">Placa física suja / desgastada</option>
                <option value="Ângulo de captura inadequado">Ângulo de captura inadequado</option>
            </select>
        </div>

        <x-slot:footer>
            <button 
                type="button" 
                @click="document.body.classList.remove('overflow-hidden')"
                wire:click="closeCorrectionModal"
                class="px-4 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold transition-colors cursor-pointer"
            >
                Cancelar
            </button>
            <button 
                type="button" 
                @click="document.body.classList.remove('overflow-hidden')"
                wire:click="confirmCorrection"
                class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">verified</span>
                <span>Confirmar Correção e Liberar Cancela</span>
            </button>
        </x-slot:footer>
    </x-modal>
@endif
