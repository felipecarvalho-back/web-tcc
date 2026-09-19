<div class="flex flex-col gap-6">
    <!-- TOAST NOTIFICATION -->
    <x-toast :message="$toastMessage" :type="$toastType" />

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

    <!-- MODAL DE CADASTRO / EDIÇÃO COM SUPORTE A N CARROS (COMPONENTE PARCIAL) -->
    @include('livewire.admin.partials.modal-condutor')
</div>
