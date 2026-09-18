<div class="flex flex-col gap-6">
    <!-- TOAST NOTIFICATION -->
    @if ($toastMessage)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => { show = false; $wire.clearToast(); }, 4000)"
            class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-semibold transition-all {{ $toastType === 'success' ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-surface-container-highest text-on-surface border-surface-container' }}"
        >
            <span class="material-symbols-outlined text-[20px]">
                {{ $toastType === 'success' ? 'check_circle' : 'info' }}
            </span>
            <span>{{ $toastMessage }}</span>
            <button type="button" @click="show = false; $wire.clearToast()" class="ml-2 text-white/80 hover:text-white">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @endif

    <!-- CONTEXTO TOPO -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-xs">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded bg-primary-container text-white text-[11px] font-bold uppercase tracking-wider">
                    Gestão de Pessoas
                </span>
                <span class="text-on-surface-variant text-xs font-semibold uppercase tracking-wider">
                    Administração Central
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">
                Gestão de Condutores e Veículos
            </h1>
            <p class="text-sm text-on-surface-variant">
                Cadastro e controle de acesso por código para professores, funcionários e prestadores de serviço com suporte a múltiplos veículos por condutor.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button 
                type="button" 
                wire:click="openCreateModal"
                class="px-4 py-2.5 bg-primary hover:bg-primary-container text-white text-xs font-bold rounded-xl flex items-center gap-2 shadow-xs transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                <span>+ Novo Condutor</span>
            </button>
        </div>
    </div>

    <!-- ABAS POR CATEGORIA -->
    <div class="flex flex-wrap gap-2 border-b border-surface-container pb-2">
        <button 
            type="button" 
            wire:click="$set('activeTab', 'professores')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $activeTab === 'professores' ? 'bg-primary text-white shadow-xs' : 'bg-surface-container hover:bg-surface-container-high text-on-surface' }}"
        >
            <span class="material-symbols-outlined text-[18px]">school</span>
            <span>Professores (Docentes)</span>
        </button>

        <button 
            type="button" 
            wire:click="$set('activeTab', 'funcionarios')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $activeTab === 'funcionarios' ? 'bg-primary text-white shadow-xs' : 'bg-surface-container hover:bg-surface-container-high text-on-surface' }}"
        >
            <span class="material-symbols-outlined text-[18px]">badge</span>
            <span>Funcionários Administrativos</span>
        </button>

        <button 
            type="button" 
            wire:click="$set('activeTab', 'prestadores')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $activeTab === 'prestadores' ? 'bg-primary text-white shadow-xs' : 'bg-surface-container hover:bg-surface-container-high text-on-surface' }}"
        >
            <span class="material-symbols-outlined text-[18px]">handyman</span>
            <span>Prestadores de Serviço</span>
        </button>
    </div>

    <!-- BARRA DE PESQUISA & FILTROS -->
    <div class="bg-surface-container-lowest p-4 rounded-2xl border border-surface-container shadow-xs flex flex-col sm:flex-row gap-3 items-center justify-between">
        <div class="flex-1 w-full sm:w-auto relative">
            <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant text-[20px]">search</span>
            <input 
                wire:model.live.debounce.300ms="search" 
                type="text" 
                placeholder="Pesquisar por nome, código, departamento ou qualquer placa..." 
                class="w-full h-10 pl-10 pr-4 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-medium rounded-xl focus:outline-none focus:border-primary"
            />
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <select 
                wire:model.live="statusFilter"
                class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer w-full sm:w-auto"
            >
                <option value="todos">Todos os Status</option>
                <option value="ativo">Apenas Ativos</option>
                <option value="inativo">Apenas Inativos</option>
            </select>
        </div>
    </div>

    <!-- TABELA DE CONDUTORES COM SUPORTE A N VEÍCULOS -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs overflow-hidden flex flex-col">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container/60 text-on-surface-variant uppercase text-[11px] font-bold tracking-wider h-11 border-b border-surface-container">
                        <th class="py-2.5 px-4">Condutor</th>
                        <th class="py-2.5 px-4">Código de Acesso</th>
                        <th class="py-2.5 px-4">Veículos Cadastrados (N)</th>
                        <th class="py-2.5 px-4">Departamento / Setor</th>
                        <th class="py-2.5 px-4">Validade</th>
                        <th class="py-2.5 px-4">Status</th>
                        <th class="py-2.5 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container text-xs">
                    @forelse ($filteredPeople as $person)
                        <tr class="hover:bg-surface-container-low/60 transition-colors">
                            <!-- Condutor -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ substr($person['name'], 0, 2) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-on-surface text-sm">{{ $person['name'] }}</span>
                                        <span class="text-on-surface-variant text-[11px]">
                                            {{ count($person['vehicles']) }} veículo(s) associado(s)
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Código de Acesso -->
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-md bg-surface-container-high border border-surface-container text-on-surface font-mono font-bold text-xs">
                                    {{ $person['code'] }}
                                </span>
                            </td>

                            <!-- Veículos (N Carros) -->
                            <td class="py-3 px-4">
                                <div class="flex flex-wrap gap-2 items-center max-w-md">
                                    @foreach ($person['vehicles'] as $v)
                                        <div class="inline-flex items-center gap-1.5 px-2 py-1 bg-surface-container-low border border-surface-container rounded-lg">
                                            <!-- Mini card placa Mercosul -->
                                            <div class="inline-flex flex-col rounded bg-white border border-gray-300 overflow-hidden w-16 text-center shrink-0">
                                                <div class="bg-blue-800 text-white text-[6px] font-bold py-0.2 tracking-wider uppercase">
                                                    BR
                                                </div>
                                                <div class="text-[9px] font-extrabold text-gray-900 py-0.2 font-mono">
                                                    {{ $v['plate'] }}
                                                </div>
                                            </div>
                                            <span class="text-[11px] text-on-surface font-medium truncate max-w-[120px]" title="{{ $v['model'] }} ({{ $v['color'] }})">
                                                {{ $v['model'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            <!-- Departamento -->
                            <td class="py-3 px-4 text-on-surface-variant">
                                {{ $person['department'] }}
                            </td>

                            <!-- Validade -->
                            <td class="py-3 px-4 font-mono text-on-surface font-semibold">
                                {{ $person['validity'] }}
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                <button 
                                    type="button" 
                                    wire:click="toggleStatus({{ $person['id'] }})"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold cursor-pointer transition-colors {{ $person['status'] === 'ativo' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest' }}"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full {{ $person['status'] === 'ativo' ? 'bg-emerald-600' : 'bg-gray-400' }}"></span>
                                    <span>{{ ucfirst($person['status']) }}</span>
                                </button>
                            </td>

                            <!-- Ações -->
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1 justify-end">
                                    <button 
                                        type="button" 
                                        wire:click="openEditModal({{ $person['id'] }})"
                                        class="p-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container transition-colors cursor-pointer"
                                        title="Editar Registro e Veículos"
                                    >
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </button>
                                    <button 
                                        type="button" 
                                        wire:click="delete({{ $person['id'] }})"
                                        wire:confirm="Deseja realmente remover este cadastro e seus veículos?"
                                        class="p-1.5 rounded-lg text-on-surface-variant hover:text-error hover:bg-error-container transition-colors cursor-pointer"
                                        title="Excluir Registro"
                                    >
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[40px] text-on-surface-variant/60 mb-2">person_off</span>
                                <p class="text-sm font-semibold">Nenhum condutor encontrado nesta categoria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-surface-container-low border-t border-surface-container flex items-center justify-between text-xs text-on-surface-variant">
            <span>Mostrando <strong class="text-on-surface font-bold">{{ count($filteredPeople) }}</strong> cadastros com seus respectivos veículos.</span>
        </div>
    </div>

    <!-- MODAL DE CADASTRO / EDIÇÃO COM SUPORTE A N CARROS -->
    @if ($showFormModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-2xl bg-surface-container-lowest rounded-2xl shadow-2xl border border-surface-container overflow-hidden flex flex-col animate-in fade-in zoom-in duration-150">
                <!-- Header -->
                <div class="px-6 py-4 bg-primary text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center text-white">
                            <span class="material-symbols-outlined text-[22px]">badge</span>
                        </div>
                        <div class="flex flex-col">
                            <h3 class="text-base font-bold text-white leading-tight">
                                {{ $editingId ? 'Editar Cadastro de Condutor' : 'Novo Condutor' }}
                            </h3>
                            <span class="text-xs text-white/80">O condutor pode possuir múltiplos veículos vinculados</span>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        wire:click="closeFormModal"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-white/80 hover:text-white hover:bg-white/10 transition-colors cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <!-- Form Body -->
                <form wire:submit="save" class="p-6 flex flex-col gap-4 max-h-[80vh] overflow-y-auto">
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

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-container mt-2">
                        <button 
                            type="button" 
                            wire:click="closeFormModal"
                            class="px-4 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold transition-colors cursor-pointer"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer"
                        >
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            <span>{{ $editingId ? 'Salvar Alterações' : 'Cadastrar Condutor' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
