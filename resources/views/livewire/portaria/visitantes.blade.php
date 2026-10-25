<div class="flex flex-col gap-6">
    <!-- TOAST NOTIFICATION -->
    <x-toast :message="$toastMessage" :type="$toastType" />

    <!-- CONTEXTO TOPO -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-xs">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded bg-primary-container text-white text-[11px] font-bold uppercase tracking-wider">
                    Posto da Guarita
                </span>
                <span class="text-on-surface-variant text-xs font-semibold uppercase tracking-wider">
                    Liberação Pontual
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">
                Gestão e Controle de Visitantes
            </h1>
            <p class="text-sm text-on-surface-variant">
                Consulta, filtro e liberação rápida de visitantes e veículos não credenciados no campus.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('portaria.monitoramento') }}"
                wire:navigate
                class="px-4 py-2.5 bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold rounded-xl flex items-center gap-1.5 transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Monitoramento</span>
            </a>

            <button
                type="button"
                wire:click="openCreateModal"
                class="px-4 py-2.5 bg-primary hover:bg-primary-container text-white text-xs font-bold rounded-xl flex items-center gap-2 shadow-xs transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                <span>+ Novo Visitante</span>
            </button>
        </div>
    </div>

    <!-- BARRA DE PESQUISA & FILTROS (BUSCA, PERÍODO E STATUS) -->
    <div class="bg-surface-container-lowest p-4 rounded-2xl border border-surface-container shadow-xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
        <!-- Busca por Placa, CPF ou Nome (6 colunas) -->
        <div class="lg:col-span-6 relative flex items-center">
            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">search</span>
            <input
                wire:model.live.debounce.300ms="searchQuery"
                type="text"
                placeholder="Pesquisar por placa, CPF ou nome do visitante..."
                class="w-full h-10 pl-10 pr-4 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-medium rounded-xl focus:outline-none focus:border-primary transition-colors"
            />
        </div>

        <!-- Filtro de Período (3 colunas) -->
        <div class="lg:col-span-3">
            <select
                wire:model.live="dateFilter"
                class="w-full h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer"
            >
                <option value="hoje">Período: Hoje</option>
                <option value="ontem">Período: Ontem</option>
                <option value="7dias">Últimos 7 dias</option>
                <option value="mes">Este Mês</option>
                <option value="todos">Todo o Histórico</option>
            </select>
        </div>

        <!-- Filtro de Status (3 colunas) -->
        <div class="lg:col-span-3">
            <select
                wire:model.live="statusFilter"
                class="w-full h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer"
            >
                <option value="todos">Status: Todos</option>
                <option value="ativo">No Campus (Ativos)</option>
                <option value="finalizado">Saída Registrada</option>
            </select>
        </div>
    </div>

    <!-- TABELA DE VISITANTES -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs overflow-hidden flex flex-col">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container/60 text-on-surface-variant uppercase text-[11px] font-bold tracking-wider h-11 border-b border-surface-container">
                        <th class="py-2.5 px-4">Placa</th>
                        <th class="py-2.5 px-4">Visitante / CPF</th>
                        <th class="py-2.5 px-4">Setor & Motivo da Visita</th>
                        <th class="py-2.5 px-4">Entrada / Saída</th>
                        <th class="py-2.5 px-4">Status</th>
                        <th class="py-2.5 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container text-xs">
                    @forelse ($filteredVisitors as $item)
                        <tr wire:key="visitor-row-{{ $item->id }}" class="hover:bg-surface-container-low/60 transition-colors">
                            <!-- Placa -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <x-mercosul-plate :plate="$item->placa_veiculo" size="sm" />
                            </td>

                            <!-- Visitante / CPF -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shrink-0 uppercase">
                                        {{ substr($item->nome, 0, 2) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-bold text-on-surface text-sm truncate">{{ $item->nome }}</span>
                                        <span class="text-[11px] text-on-surface-variant font-mono">CPF: {{ $item->cpf }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Setor & Motivo -->
                            <td class="py-3 px-4 max-w-[260px]">
                                <div class="font-semibold text-on-surface truncate">{{ $item->setor_destino }}</div>
                                <div class="text-[11px] text-on-surface-variant truncate" title="{{ $item->destino_motivo }}">
                                    {{ $item->destino_motivo }}
                                </div>
                            </td>

                            <!-- Entrada / Saída -->
                            <td class="py-3 px-4 font-mono whitespace-nowrap">
                                <div class="text-on-surface font-semibold">
                                    {{ $item->created_at->format('d/m H:i:s') }}
                                </div>
                                @if ($item->data_hora_saida)
                                    <div class="text-[11px] text-on-surface-variant">
                                        Saída: {{ $item->data_hora_saida->format('H:i:s') }}
                                    </div>
                                @else
                                    <div class="text-[11px] text-emerald-700 font-sans font-semibold">
                                        Previsto: {{ $item->permanencia_estimada }}
                                    </div>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($item->status === 'ativo')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        <span>No Campus</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container-high text-on-surface-variant text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        <span>Finalizado</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Ações -->
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <button
                                    type="button"
                                    wire:click="editVisitor({{ $item->id }})"
                                    title="Editar Registro"
                                    class="p-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container transition-colors cursor-pointer"
                                >
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[40px] text-on-surface-variant/60 mb-2">person_off</span>
                                <p class="text-sm font-semibold">Nenhum registro de visitante encontrado para os filtros selecionados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-surface-container-low border-t border-surface-container flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-on-surface-variant">
            <span>
                Mostrando <strong class="text-on-surface font-bold">{{ $filteredVisitors->count() }}</strong> de <strong class="text-on-surface font-bold">{{ $filteredVisitors->total() }}</strong> registros de passagens de visitantes.
            </span>

            @if ($filteredVisitors->hasPages())
                <div>
                    {{ $filteredVisitors->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL DE CADASTRO / EDIÇÃO DE VISITANTE (ARQUIVO SEPARADO) -->
    @include('livewire.portaria.partials.modal-visitante')
</div>