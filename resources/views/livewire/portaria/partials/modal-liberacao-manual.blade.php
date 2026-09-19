@if ($showManualEntryModal && $selectedRecord)
    <x-modal 
        title="Liberação Manual de Acesso - Docente FATEC"
        subtitle="Autorização Excepcional para Veículo Não Cadastrado"
        icon="badge"
        onClose="closeManualEntryModal"
        maxWidth="2xl"
    >
        <!-- Resumo do Veículo na Cancela -->
        <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-16 h-12 rounded-lg overflow-hidden bg-black shrink-0 border border-surface-container">
                    <img src="{{ $selectedRecord['image_url'] }}" alt="Captura da Cancela" class="w-full h-full object-cover">
                </div>
                <div class="flex flex-col">
                    <span class="text-[11px] text-on-surface-variant font-semibold uppercase">Veículo na Cancela 01</span>
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

        <!-- Orientações operacionais -->
        <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-xs flex items-start gap-2.5">
            <span class="material-symbols-outlined text-[20px] text-blue-700 shrink-0 mt-0.5">info</span>
            <div class="flex flex-col gap-0.5">
                <span class="font-bold">Regra de Acesso Excepcional para Docentes</span>
                <p class="text-[11.5px] leading-relaxed text-blue-800">
                    Utilize esta opção caso o professor esteja utilizando um <strong>carro de terceiro, amigo, parente ou veículo reserva</strong> que não consta em seu cadastro permanente. Digite o código funcional do docente para validar a autorização e liberar a cancela.
                </p>
            </div>
        </div>

        <!-- Input do Código de Acesso do Professor -->
        <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-on-surface flex items-center justify-between" for="professor-access-code">
                <span>Código de Acesso Funcional / Matrícula do Docente *</span>
                <span class="text-[11px] font-normal text-on-surface-variant">Ex: DOC-94281 ou apenas 94281</span>
            </label>

            <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">pin</span>
                <input 
                    wire:model.live.debounce.300ms="professorAccessCode"
                    id="professor-access-code"
                    type="text" 
                    placeholder="Digite o código (Ex: DOC-94281)" 
                    class="w-full h-11 pl-10 pr-4 bg-surface-container-lowest border-2 {{ $identifiedProfessor ? 'border-emerald-500 ring-2 ring-emerald-500/20' : ($manualEntryError ? 'border-error' : 'border-surface-container-highest focus:border-primary') }} text-on-surface text-sm font-mono font-bold rounded-xl focus:outline-none uppercase tracking-wider transition-all"
                    autofocus
                />
            </div>

            @if ($manualEntryError)
                <span class="text-error text-xs font-semibold flex items-center gap-1 mt-0.5">
                    <span class="material-symbols-outlined text-[15px]">error</span>
                    <span>{{ $manualEntryError }}</span>
                </span>
            @endif
        </div>

        <!-- Códigos Rápidos de Demonstração / Operação -->
        <div class="flex flex-col gap-1.5">
            <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">
                Docentes Cadastrados no Sistema (Clique para preencher rápido):
            </span>
            <div class="flex flex-wrap gap-2">
                @foreach ($authorizedProfessors as $prof)
                    <button 
                        type="button" 
                        wire:click="selectProfessorCode('{{ $prof['code'] }}')"
                        class="px-2.5 py-1.5 rounded-lg border text-xs font-medium flex items-center gap-1.5 transition-all cursor-pointer {{ $professorAccessCode === $prof['code'] ? 'bg-primary text-white border-primary shadow-xs' : 'bg-surface-container-low hover:bg-surface-container text-on-surface border-surface-container' }}"
                    >
                        <span class="font-mono font-bold">{{ $prof['code'] }}</span>
                        <span class="text-on-surface-variant text-[11px] {{ $professorAccessCode === $prof['code'] ? 'text-white/80' : '' }}">• {{ $prof['name'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Card de Confirmação do Professor Localizado -->
        @if ($identifiedProfessor)
            <div class="p-4 rounded-xl bg-emerald-50 border-2 border-emerald-500/50 flex items-center justify-between gap-3 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <span class="material-symbols-outlined text-[24px]">verified</span>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <h4 class="text-sm font-bold text-emerald-950">{{ $identifiedProfessor['name'] }}</h4>
                            <span class="px-2 py-0.5 rounded bg-emerald-200 text-emerald-900 text-[10px] font-extrabold uppercase">
                                {{ $identifiedProfessor['code'] }}
                            </span>
                        </div>
                        <span class="text-xs text-emerald-800">{{ $identifiedProfessor['department'] }}</span>
                    </div>
                </div>

                <span class="px-2.5 py-1 rounded-full bg-emerald-600 text-white text-[11px] font-bold shrink-0 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">check</span>
                    <span>Docente Validado</span>
                </span>
            </div>
        @endif

        <!-- Justificativa da Liberação Manual -->
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold text-on-surface" for="manual-entry-justification">
                Motivo / Justificativa do Acesso
            </label>
            <select 
                wire:model="manualEntryJustification"
                id="manual-entry-justification"
                class="w-full h-11 px-3 bg-surface-container-lowest border border-surface-container-highest text-on-surface text-xs font-medium rounded-xl focus:outline-none focus:border-primary cursor-pointer"
            >
                <option value="Veículo de terceiro / Carro emprestado de amigo ou parente">Veículo de terceiro / Carro emprestado de amigo ou parente</option>
                <option value="Carro novo / Aguardando cadastro permanente no sistema">Carro novo / Aguardando cadastro permanente no sistema</option>
                <option value="Veículo reserva / Carro alugado / Oficina mecânica">Veículo reserva / Carro alugado / Oficina mecânica</option>
                <option value="Docente convidado / Reunião com Coordenação">Docente convidado / Reunião com Coordenação</option>
                <option value="Outros motivos operacionais da guarita">Outros motivos operacionais da guarita</option>
            </select>
        </div>

        <x-slot:footer>
            <button 
                type="button" 
                @click="document.body.classList.remove('overflow-hidden')"
                wire:click="closeManualEntryModal"
                class="px-4 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold transition-colors cursor-pointer"
            >
                Cancelar
            </button>
            <button 
                type="button" 
                wire:click="confirmManualEntry"
                class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">garage</span>
                <span>Confirmar Código e Liberar Cancela</span>
            </button>
        </x-slot:footer>
    </x-modal>
@endif
