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
                        placeholder="Ex: DOC-10492"
                        class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface font-mono font-bold text-xs uppercase rounded-xl focus:outline-none focus:border-primary"
                    />
                    @error('code') <span class="text-error text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Departamento / Empresa & Validade -->
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

                $opcoesValidade = [
                    'Indeterminado' => 'Indeterminado (Acesso Permanente)',
                    'Semestre Atual' => 'Semestre Letivo Atual (6 meses)',
                    '1 Ano' => 'Anual (12 meses)',
                    '90 Dias' => 'Trimestral (90 dias)',
                    '30 Dias' => 'Temporário (30 dias)',
                    '7 Dias' => 'Semanal (7 dias)',
                ];
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface" for="condutor-validity">
                        Prazo de Autorização
                    </label>
                    <select
                        wire:model="accessValidity"
                        id="condutor-validity"
                        class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer"
                    >
                        @if ($accessValidity !== '' && ! array_key_exists($accessValidity, $opcoesValidade))
                            <option value="{{ $accessValidity }}">{{ $accessValidity }}</option>
                        @endif
                        @foreach ($opcoesValidade as $valor => $rotulo)
                            <option value="{{ $valor }}">{{ $rotulo }}</option>
                        @endforeach
                    </select>
                    @error('accessValidity') <span class="text-error text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>
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
