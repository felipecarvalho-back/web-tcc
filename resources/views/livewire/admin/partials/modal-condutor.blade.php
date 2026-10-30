@if ($showFormModal)
    <x-modal
        title="{{ $editingId ? 'Editar Cadastro de Condutor' : 'Novo Condutor' }}"
        subtitle="O condutor pode possuir múltiplos veículos vinculados"
        icon="badge"
        onClose="closeFormModal"
        maxWidth="2xl"
    >
        <form wire:submit="save" id="form-condutor" class="flex flex-col gap-4">
            <!-- Categoria e Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface">Tipo / Categoria *</label>
                    <select
                        wire:model.live="category"
                        class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer"
                    >
                        <option value="professor">Professor (Docente FATEC)</option>
                        <option value="funcionario">Funcionário Administrativo</option>
                        <option value="prestador">Prestador de Serviço</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface">Status de Acesso *</label>
                    <select
                        wire:model="status"
                        class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer"
                    >
                        <option value="ativo">Ativo (Acesso Liberado)</option>
                        <option value="inativo">Inativo (Bloqueado na Guarita)</option>
                    </select>
                </div>
            </div>

            <!-- Nome, CPF & Código de Acesso -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-5 flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface">Nome Completo *</label>
                    <input
                        wire:model="name"
                        type="text"
                        placeholder="Ex: Prof. Dr. André Silva"
                        class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs rounded-xl focus:outline-none focus:border-primary"
                    />
                    @error('name') <span class="text-error text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-4 flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface">CPF *</label>
                    <input
                        wire:model="cpf"
                        type="text"
                        x-mask="999.999.999-99"
                        inputmode="numeric"
                        maxlength="14"
                        placeholder="000.000.000-00"
                        class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface font-mono text-xs rounded-xl focus:outline-none focus:border-primary"
                    />
                    @error('cpf') <span class="text-error text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3 flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface">Código / Matrícula *</label>
                    <input
                        wire:model="code"
                        type="text"
                        x-mask="***-*****"
                        maxlength="9"
                        placeholder="Ex: DOC-10492"
                        class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface font-mono font-bold text-xs uppercase rounded-xl focus:outline-none focus:border-primary"
                    />
                    @error('code') <span class="text-error text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Departamento / Empresa -->
            @php
                $opcoesDepartamento = $category === 'prestador'
                    ? [
                        'Manutenção Predial / Infraestrutura' => 'Manutenção Predial / Infraestrutura',
                        'Infraestrutura Elétrica & Redes' => 'Infraestrutura Elétrica & Redes',
                        'Limpeza e Conservação' => 'Limpeza e Conservação',
                        'Segurança Patrimonial' => 'Segurança Patrimonial',
                        'Jardinagem e Zeladoria' => 'Jardinagem e Zeladoria',
                        'Suporte Técnico / TI' => 'Suporte Técnico / TI',
                    ]
                    : [
                        'DSM - Coordenação DSM' => 'Bloco A • Coordenação DSM',
                        'GTI - Coordenação GTI' => 'Bloco A • Coordenação GTI',
                        'Direção / Recursos Humanos' => 'Bloco Administrativo • Direção & RH',
                        'Secretaria Acadêmica' => 'Bloco Administrativo • Secretaria',
                        'Laboratórios Técnicos' => 'Bloco B • Laboratórios de Informática',
                        'Biblioteca / Auditório' => 'Bloco C • Biblioteca & Auditório',
                    ];
            @endphp

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-on-surface" for="condutor-department">
                    {{ $category === 'prestador' ? 'Área / Empresa Terceirizada *' : 'Setor / Departamento' }}
                </label>
                <select
                    wire:model="department"
                    id="condutor-department"
                    class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer"
                >
                    @if ($department !== '' && ! array_key_exists($department, $opcoesDepartamento))
                        <option value="{{ $department }}">{{ $department }}</option>
                    @endif
                    @foreach ($opcoesDepartamento as $valor => $rotulo)
                        <option value="{{ $valor }}">{{ $rotulo }}</option>
                    @endforeach
                </select>
                @error('department') <span class="text-error text-[11px] font-semibold">{{ $message }}</span> @enderror
            </div>

            <!-- PRAZO DE AUTORIZAÇÃO / VALIDADE DE ACESSO -->
            <div class="p-4 bg-surface-container-low rounded-xl border border-surface-container flex flex-col gap-3.5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-primary">event_upcoming</span>
                        <div>
                            <span class="text-xs font-bold text-on-surface block">Prazo de Autorização de Acesso</span>
                            <span class="text-[11px] text-on-surface-variant block">Determine o período de vigência para a liberação da cancela</span>
                        </div>
                    </div>

                    <!-- Toggle Modo: Permanente vs Prazo Determinado -->
                    <div class="inline-flex p-0.5 rounded-lg bg-surface-container border border-surface-container-highest self-start sm:self-auto">
                        <button
                            type="button"
                            wire:click="setValidityType('indeterminado')"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-md transition-colors cursor-pointer {{ $validityType === 'indeterminado' ? 'bg-primary text-white shadow-xs' : 'text-on-surface-variant hover:text-on-surface' }}"
                        >
                            Permanente
                        </button>
                        <button
                            type="button"
                            wire:click="setValidityType('data')"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-md transition-colors cursor-pointer {{ $validityType === 'data' ? 'bg-primary text-white shadow-xs' : 'text-on-surface-variant hover:text-on-surface' }}"
                        >
                            Prazo Determinado
                        </button>
                    </div>
                </div>

                @if ($validityType === 'indeterminado')
                    <div class="p-3 rounded-lg bg-surface-container-lowest border border-surface-container flex items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2 text-on-surface-variant">
                            <span class="material-symbols-outlined text-[18px] text-emerald-600">all_inclusive</span>
                            <span>Autorização por prazo indeterminado (sem data de expiração cadastrada).</span>
                        </div>
                        <button
                            type="button"
                            wire:click="applyValidityPreset('semestre')"
                            class="text-[11px] font-bold text-primary hover:underline cursor-pointer shrink-0"
                        >
                            Definir data limite
                        </button>
                    </div>
                @else
                    <div class="flex flex-col gap-3">
                        <!-- Opções Pré-configuradas (Presets rápidos) -->
                        <div>
                            <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block mb-1.5">
                                Opções Pré-configuradas
                            </span>
                            <div class="flex flex-wrap gap-1.5">
                                <button
                                    type="button"
                                    wire:click="applyValidityPreset('30_dias')"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition-colors cursor-pointer {{ $selectedPreset === '30_dias' ? 'bg-primary text-white border-primary' : 'bg-surface-container-lowest border-surface-container-highest text-on-surface hover:border-primary/50' }}"
                                    title="30 dias a partir de hoje"
                                >
                                    30 Dias (1 Mês)
                                </button>
                                <button
                                    type="button"
                                    wire:click="applyValidityPreset('90_dias')"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition-colors cursor-pointer {{ $selectedPreset === '90_dias' ? 'bg-primary text-white border-primary' : 'bg-surface-container-lowest border-surface-container-highest text-on-surface hover:border-primary/50' }}"
                                    title="90 dias a partir de hoje"
                                >
                                    90 Dias (Trimestral)
                                </button>
                                <button
                                    type="button"
                                    wire:click="applyValidityPreset('semestre')"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition-colors cursor-pointer {{ $selectedPreset === 'semestre' ? 'bg-primary text-white border-primary' : 'bg-surface-container-lowest border-surface-container-highest text-on-surface hover:border-primary/50' }}"
                                    title="Término do semestre letivo vigente"
                                >
                                    Semestre Letivo Atual
                                </button>
                                <button
                                    type="button"
                                    wire:click="applyValidityPreset('1_ano')"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition-colors cursor-pointer {{ $selectedPreset === '1_ano' ? 'bg-primary text-white border-primary' : 'bg-surface-container-lowest border-surface-container-highest text-on-surface hover:border-primary/50' }}"
                                    title="1 ano a partir de hoje"
                                >
                                    1 Ano (12 Meses)
                                </button>
                                <button
                                    type="button"
                                    wire:click="applyValidityPreset('fim_ano')"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition-colors cursor-pointer {{ $selectedPreset === 'fim_ano' ? 'bg-primary text-white border-primary' : 'bg-surface-container-lowest border-surface-container-highest text-on-surface hover:border-primary/50' }}"
                                    title="31 de Dezembro do ano atual"
                                >
                                    Até o Fim do Ano
                                </button>
                                <button
                                    type="button"
                                    wire:click="applyValidityPreset('indeterminado')"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-lg border border-surface-container-highest text-on-surface-variant hover:text-on-surface hover:border-primary/50 bg-surface-container-lowest cursor-pointer"
                                >
                                    Sem Validade (Permanente)
                                </button>
                            </div>
                        </div>

                        <!-- Campo Direto de Data para o Admin Determinar -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-end pt-1">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-on-surface" for="condutor-validity-date">
                                    Data Limite da Autorização *
                                </label>
                                <div class="relative flex items-center">
                                    <input
                                        wire:model.live="validityDate"
                                        id="condutor-validity-date"
                                        type="date"
                                        class="w-full h-11 px-3 bg-surface-container-lowest border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer font-mono"
                                    />
                                </div>
                                @error('validityDate') <span class="text-error text-[11px] font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <!-- Resumo / Feedback da data -->
                            <div class="p-2.5 rounded-xl bg-surface-container-lowest border border-surface-container flex items-center gap-2 text-xs">
                                @if (! empty($validityDate))
                                    @php
                                        try {
                                            $parsedDate = \Carbon\Carbon::parse($validityDate);
                                            $hoje = \Carbon\Carbon::today();
                                            $dias = (int) $hoje->diffInDays($parsedDate, false);
                                        } catch (\Throwable) {
                                            $parsedDate = null;
                                            $dias = null;
                                        }
                                    @endphp
                                    @if ($parsedDate)
                                        @if ($dias < 0)
                                            <span class="material-symbols-outlined text-[18px] text-error shrink-0">error</span>
                                            <span class="text-error font-semibold text-[11.5px]">
                                                Atenção: Data expirada há {{ abs($dias) }} dia(s) ({{ $parsedDate->format('d/m/Y') }}).
                                            </span>
                                        @elseif ($dias === 0)
                                            <span class="material-symbols-outlined text-[18px] text-amber-600 shrink-0">warning</span>
                                            <span class="text-amber-700 font-semibold text-[11.5px]">
                                                Vence hoje ({{ $parsedDate->format('d/m/Y') }}).
                                            </span>
                                        @else
                                            <span class="material-symbols-outlined text-[18px] text-emerald-600 shrink-0">check_circle</span>
                                            <span class="text-on-surface font-semibold text-[11.5px]">
                                                Válido até <strong>{{ $parsedDate->format('d/m/Y') }}</strong> (restam {{ $dias }} dias).
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-on-surface-variant text-[11.5px]">Escolha uma data limite válida.</span>
                                    @endif
                                @else
                                    <span class="text-on-surface-variant text-[11.5px]">Nenhuma data selecionada ainda.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- VEÍCULOS VINCULADOS (O CONDUTOR PODE TER N CARROS) -->
            <div class="p-4 bg-surface-container-low rounded-xl border border-surface-container flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-primary uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">directions_car</span>
                        <span>Veículos Cadastrados ({{ count($vehicles) }})</span>
                    </span>
                    <button
                        type="button"
                        wire:click="addVehicle"
                        class="px-2.5 py-1 bg-primary text-white text-[11px] font-bold rounded-lg flex items-center gap-1 hover:bg-primary-container transition-colors cursor-pointer shadow-xs"
                    >
                        <span class="material-symbols-outlined text-[15px]">add</span>
                        <span>+ Adicionar outro veículo</span>
                    </button>
                </div>

                <div class="flex flex-col gap-3">
                    @foreach ($vehicles as $index => $v)
                        <div wire:key="modal-vehicle-{{ $index }}" class="p-3 bg-surface-container-lowest rounded-xl border border-surface-container flex flex-col gap-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-on-surface-variant font-mono">
                                    Veículo #{{ $index + 1 }}
                                </span>

                                @if (count($vehicles) > 1)
                                    <button
                                        type="button"
                                        wire:click="removeVehicle({{ $index }})"
                                        class="p-1.5 text-on-surface-variant hover:text-error rounded-lg hover:bg-error-container/20 transition-colors cursor-pointer"
                                        title="Remover este veículo"
                                    >
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                <!-- Placa -->
                                <div class="flex flex-col gap-1">
                                    <label class="text-[11px] font-bold text-on-surface">Placa <span class="text-error">*</span></label>
                                    <input
                                        wire:model="vehicles.{{ $index }}.plate"
                                        type="text"
                                        x-mask="***-****"
                                        maxlength="8"
                                        placeholder="Ex: ABC-1234"
                                        class="w-full h-9 px-2.5 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-mono font-bold uppercase rounded-lg focus:outline-none focus:border-primary"
                                    />
                                    @error("vehicles.{$index}.plate")
                                        <span class="text-error text-[10px] font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Modelo -->
                                <div class="flex flex-col gap-1">
                                    <label class="text-[11px] font-bold text-on-surface">Modelo <span class="text-error">*</span></label>
                                    <input
                                        wire:model="vehicles.{{ $index }}.model"
                                        type="text"
                                        placeholder="Ex: Honda Civic"
                                        class="w-full h-9 px-2.5 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs rounded-lg focus:outline-none focus:border-primary"
                                    />
                                    @error("vehicles.{$index}.model")
                                        <span class="text-error text-[10px] font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Cor -->
                                <div class="flex flex-col gap-1">
                                    <label class="text-[11px] font-bold text-on-surface">Cor</label>
                                    <input
                                        wire:model="vehicles.{{ $index }}.color"
                                        type="text"
                                        placeholder="Ex: Prata"
                                        class="w-full h-9 px-2.5 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs rounded-lg focus:outline-none focus:border-primary"
                                    />
                                    @error("vehicles.{$index}.color")
                                        <span class="text-error text-[10px] font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button
                type="button"
                wire:click="closeFormModal"
                class="px-4 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold transition-colors cursor-pointer"
            >
                Cancelar
            </button>
            <button
                type="submit"
                form="form-condutor"
                wire:loading.attr="disabled"
                class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container disabled:opacity-50 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span>{{ $editingId ? 'Salvar Alterações' : 'Cadastrar Condutor' }}</span>
            </button>
        </x-slot:footer>
    </x-modal>
@endif
