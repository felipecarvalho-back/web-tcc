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
                        wire:model="category" 
                        class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary"
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
                        class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary"
                    >
                        <option value="ativo">Ativo (Acesso Liberado)</option>
                        <option value="inativo">Inativo (Bloqueado na Guarita)</option>
                    </select>
                </div>
            </div>

            <!-- Nome & Código de Acesso (Sem e-mail) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface">Nome Completo *</label>
                    <input 
                        wire:model="name" 
                        type="text" 
                        placeholder="Ex: Prof. Dr. André Silva" 
                        class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs rounded-xl focus:outline-none focus:border-primary"
                    />
                    @error('name') <span class="text-error text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface">Código de Acesso / Matrícula *</label>
                    <input 
                        wire:model="code" 
                        type="text" 
                        placeholder="Ex: DOC-10492" 
                        class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface font-mono font-bold text-xs uppercase rounded-xl focus:outline-none focus:border-primary"
                    />
                    @error('code') <span class="text-error text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Departamento & Validade -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface">Setor / Departamento</label>
                    <input 
                        wire:model="department" 
                        type="text" 
                        placeholder="Ex: Coordenação DSM, Secretaria..." 
                        class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs rounded-xl focus:outline-none focus:border-primary"
                    />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface">Prazo de Autorização</label>
                    <input 
                        wire:model="accessValidity" 
                        type="text" 
                        placeholder="Ex: Indeterminado, 31/12/2026..." 
                        class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs rounded-xl focus:outline-none focus:border-primary"
                    />
                </div>
            </div>

            <!-- VEÍCULOS VINCULADOS (O PROFESSOR PODE TER N CARROS) -->
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

                @error('vehicles.0.plate') 
                    <span class="text-error text-[11px] font-semibold">{{ $message }}</span> 
                @enderror

                <div class="flex flex-col gap-3">
                    @foreach ($vehicles as $index => $v)
                        <div class="p-3 bg-surface-container-lowest rounded-xl border border-surface-container flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
                            <span class="text-xs font-bold text-on-surface-variant font-mono sm:w-6">
                                #{{ $index + 1 }}
                            </span>

                            <!-- Placa -->
                            <div class="flex-1">
                                <input 
                                    wire:model="vehicles.{{ $index }}.plate" 
                                    type="text" 
                                    placeholder="Placa (Ex: BRA-2E19)" 
                                    class="w-full h-9 px-2.5 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-mono font-bold uppercase rounded-lg focus:outline-none focus:border-primary"
                                />
                            </div>

                            <!-- Modelo -->
                            <div class="flex-1">
                                <input 
                                    wire:model="vehicles.{{ $index }}.model" 
                                    type="text" 
                                    placeholder="Modelo (Ex: Corolla)" 
                                    class="w-full h-9 px-2.5 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs rounded-lg focus:outline-none focus:border-primary"
                                />
                            </div>

                            <!-- Cor -->
                            <div class="w-24">
                                <input 
                                    wire:model="vehicles.{{ $index }}.color" 
                                    type="text" 
                                    placeholder="Cor" 
                                    class="w-full h-9 px-2.5 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs rounded-lg focus:outline-none focus:border-primary"
                                />
                            </div>

                            <!-- Remover se houver mais de 1 -->
                            @if (count($vehicles) > 1)
                                <button 
                                    type="button" 
                                    wire:click="removeVehicle({{ $index }})"
                                    class="p-1.5 text-on-surface-variant hover:text-error rounded-lg hover:bg-error-container/20 transition-colors cursor-pointer self-end sm:self-center"
                                    title="Remover este veículo"
                                >
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button 
                type="button" 
                @click="document.body.classList.remove('overflow-hidden')"
                wire:click="closeFormModal"
                class="px-4 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold transition-colors cursor-pointer"
            >
                Cancelar
            </button>
            <button 
                type="submit" 
                form="form-condutor"
                class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span>{{ $editingId ? 'Salvar Alterações' : 'Cadastrar Condutor' }}</span>
            </button>
        </x-slot:footer>
    </x-modal>
@endif
