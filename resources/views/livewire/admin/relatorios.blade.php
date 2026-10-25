<div class="flex flex-col gap-6">
    <!-- ÁREA INTERATIVA WEB (OCULTA NA IMPRESSÃO) -->
    <div class="flex flex-col gap-6 no-print">
        <!-- CABEÇALHO DE RELATÓRIOS -->
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-xs">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded bg-primary-container text-white text-[11px] font-bold uppercase tracking-wider">
                        Auditoria & Tráfego
                    </span>
                    <span class="text-on-surface-variant text-xs font-semibold uppercase tracking-wider">
                        Exportação em PDF & CSV
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">
                    Relatórios Gerenciais de Tráfego
                </h1>
                <p class="text-sm text-on-surface-variant">
                    Auditoria analítica de entradas, saídas, permanência no campus e conferência de assiduidade docente.
                </p>
            </div>

            <!-- BOTÕES DE AÇÃO E EXPORTAÇÃO -->
            <div class="flex flex-wrap items-center gap-2.5">
                <button
                    type="button"
                    wire:click="openPreview"
                    class="px-4 py-2.5 bg-surface-container-low hover:bg-surface-container text-on-surface text-xs font-bold rounded-xl flex items-center gap-2 border border-surface-container-highest transition-colors cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[19px] text-primary">visibility</span>
                    <span>Visualizar Documento</span>
                </button>

                <button
                    type="button"
                    wire:click="exportCsv"
                    wire:loading.attr="disabled"
                    class="px-4 py-2.5 bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold rounded-xl flex items-center gap-2 border border-surface-container-highest transition-colors cursor-pointer disabled:opacity-50"
                >
                    <span class="material-symbols-outlined text-[19px] text-emerald-700">table_view</span>
                    <span wire:loading.remove wire:target="exportCsv">Exportar Planilha (CSV)</span>
                    <span wire:loading wire:target="exportCsv">Gerando CSV...</span>
                </button>

                <button
                    type="button"
                    onclick="window.print()"
                    class="px-5 py-2.5 bg-primary hover:bg-primary-container text-white text-xs font-bold rounded-xl flex items-center gap-2 shadow-sm hover:shadow-md transition-all cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[19px]">picture_as_pdf</span>
                    <span>Gerar Relatório em PDF</span>
                </button>
            </div>
        </div>

        <!-- 4 INDICADORES EXECUTIVOS DO PERÍODO FILTRADO -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Filtrado -->
            <div class="p-5 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Registros Filtrados
                    </span>
                    <div class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center text-on-surface">
                        <span class="material-symbols-outlined text-[22px]">receipt_long</span>
                    </div>
                </div>
                <div>
                    <div class="text-3xl font-extrabold text-on-surface font-mono">{{ $reportStats['total'] }}</div>
                    <span class="text-xs text-on-surface-variant mt-1 block">
                        Passagens localizadas na consulta
                    </span>
                </div>
            </div>

            <!-- Entradas Ativas / No Pátio -->
            <div class="p-5 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Em Pátio (Entradas)
                    </span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-700">
                        <span class="material-symbols-outlined text-[22px]">input</span>
                    </div>
                </div>
                <div>
                    <div class="text-3xl font-extrabold text-emerald-700 font-mono">{{ $reportStats['entradas'] }}</div>
                    <span class="text-xs text-on-surface-variant mt-1 block">
                        Acessos ativos sem baixa de saída
                    </span>
                </div>
            </div>

            <!-- Saídas Concluídas -->
            <div class="p-5 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Saídas Concluídas
                    </span>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-700">
                        <span class="material-symbols-outlined text-[22px]">output</span>
                    </div>
                </div>
                <div>
                    <div class="text-3xl font-extrabold text-amber-700 font-mono">{{ $reportStats['saidas'] }}</div>
                    <span class="text-xs text-on-surface-variant mt-1 block">
                        Ciclos de permanência encerrados
                    </span>
                </div>
            </div>

            <!-- Docentes Identificados -->
            <div class="p-5 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Acessos Docentes
                    </span>
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-[22px]">school</span>
                    </div>
                </div>
                <div>
                    <div class="text-3xl font-extrabold text-primary font-mono">{{ $reportStats['docentes'] }}</div>
                    <span class="text-xs text-on-surface-variant mt-1 block">
                        Passagens de professores no período
                    </span>
                </div>
            </div>
        </div>

        <!-- PAINEL DE FILTROS ORGANIZADO EM 2 LINHAS -->
        <div class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container shadow-xs flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-surface-container gap-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">tune</span>
                    <h2 class="text-xs font-bold text-on-surface uppercase tracking-wider">
                        Filtros de Tráfego e Assiduidade Docente
                    </h2>
                </div>

                <!-- Atalhos de Período + Botão Limpar -->
                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center bg-surface-container-low p-1 rounded-xl border border-surface-container">
                        @foreach (['hoje' => 'Hoje', '7dias' => 'Últimos 7 dias', 'mes' => 'Este Mês', 'ano' => 'Ano Atual'] as $key => $label)
                            <button
                                type="button"
                                wire:click="setQuickPeriod('{{ $key }}')"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer {{ $quickPeriod === $key ? 'bg-primary text-white shadow-2xs' : 'text-on-surface-variant hover:text-on-surface' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    @if ($dayOfWeekFilter !== 'todos' || !empty(trim($driverSearch)) || $categoryFilter !== 'todos' || $eventType !== 'todos' || $operatorFilter !== 'todos')
                        <button
                            type="button"
                            wire:click="clearAssiduidadeFilters"
                            class="px-3 py-1.5 rounded-xl bg-error-container/50 hover:bg-error-container text-error text-xs font-bold flex items-center gap-1 transition-colors cursor-pointer"
                        >
                            <span class="material-symbols-outlined text-[16px]">filter_alt_off</span>
                            <span>Limpar Filtros</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Linha 1: Busca Nominal/Placa + Intervalo de Datas (12 colunas) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
                <!-- Buscar Professor / Condutor / Placa (6 colunas) -->
                <div class="sm:col-span-2 lg:col-span-6 flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-on-surface-variant uppercase flex items-center gap-1">
                        <span class="material-symbols-outlined text-primary text-[15px]">person_search</span>
                        <span>Buscar por Condutor, Professor, Placa ou CPF</span>
                    </label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[18px] pointer-events-none">search</span>
                        <input
                            wire:model.live.debounce.300ms="driverSearch"
                            type="text"
                            placeholder="Digite o nome do condutor, placa Mercosul ou CPF..."
                            class="h-10 pl-9 pr-8 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary w-full transition-colors"
                        />
                        @if (!empty($driverSearch))
                            <button
                                type="button"
                                wire:click="$set('driverSearch', '')"
                                class="absolute right-2.5 text-on-surface-variant hover:text-on-surface cursor-pointer"
                                title="Limpar busca"
                            >
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Data Inicial (3 colunas) -->
                <div class="lg:col-span-3 flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-on-surface-variant uppercase flex items-center gap-1">
                        <span class="material-symbols-outlined text-primary text-[15px]">calendar_today</span>
                        <span>Data Inicial</span>
                    </label>
                    <input
                        wire:model.live="startDate"
                        type="date"
                        class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary w-full"
                    />
                </div>

                <!-- Data Final (3 colunas) -->
                <div class="lg:col-span-3 flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-on-surface-variant uppercase flex items-center gap-1">
                        <span class="material-symbols-outlined text-primary text-[15px]">event</span>
                        <span>Data Final</span>
                    </label>
                    <input
                        wire:model.live="endDate"
                        type="date"
                        class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary w-full"
                    />
                </div>
            </div>

            <!-- Linha 2: Refinamentos de Categoria, Dia da Semana, Evento e Operador (12 colunas) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
                <!-- Dia da Semana (3 colunas) -->
                <div class="lg:col-span-3 flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-on-surface-variant uppercase flex items-center gap-1">
                        <span class="material-symbols-outlined text-primary text-[15px]">event_repeat</span>
                        <span>Dia da Semana (Frequência)</span>
                    </label>
                    <select
                        wire:model.live="dayOfWeekFilter"
                        class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer w-full"
                    >
                        <option value="todos">Todos os Dias</option>
                        <option value="segunda">Segunda-feira</option>
                        <option value="terca">Terça-feira</option>
                        <option value="quarta">Quarta-feira</option>
                        <option value="quinta">Quinta-feira</option>
                        <option value="sexta">Sexta-feira</option>
                        <option value="sabado">Sábado</option>
                        <option value="domingo">Domingo</option>
                    </select>
                </div>

                <!-- Categoria (3 colunas) -->
                <div class="lg:col-span-3 flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-on-surface-variant uppercase flex items-center gap-1">
                        <span class="material-symbols-outlined text-primary text-[15px]">badge</span>
                        <span>Categoria de Vínculo</span>
                    </label>
                    <select
                        wire:model.live="categoryFilter"
                        class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer w-full"
                    >
                        <option value="todos">Todas as Categorias</option>
                        <option value="professor">Professores (Docentes)</option>
                        <option value="funcionario">Funcionários / Servidores</option>
                        <option value="prestador">Prestadores de Serviço</option>
                        <option value="visitante">Visitantes</option>
                    </select>
                </div>

                <!-- Tipo de Evento (3 colunas) -->
                <div class="lg:col-span-3 flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-on-surface-variant uppercase flex items-center gap-1">
                        <span class="material-symbols-outlined text-primary text-[15px]">swap_horiz</span>
                        <span>Status da Movimentação</span>
                    </label>
                    <select
                        wire:model.live="eventType"
                        class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer w-full"
                    >
                        <option value="todos">Todos os Eventos</option>
                        <option value="entrada">Apenas Entradas (Em Pátio)</option>
                        <option value="saida">Apenas Saídas Concluídas</option>
                    </select>
                </div>

                <!-- Operador da Guarita (3 colunas) -->
                <div class="lg:col-span-3 flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-on-surface-variant uppercase flex items-center gap-1">
                        <span class="material-symbols-outlined text-primary text-[15px]">security</span>
                        <span>Operador Responsável</span>
                    </label>
                    <select
                        wire:model.live="operatorFilter"
                        class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer w-full"
                    >
                        <option value="todos">Todos os Operadores</option>
                        @foreach ($operators as $op)
                            <option value="{{ $op['nome'] }}">{{ $op['nome'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Banner de Feedback quando filtros específicos estão ativos -->
            @if ($dayOfWeekFilter !== 'todos' || !empty(trim($driverSearch)) || $categoryFilter !== 'todos')
                <div class="p-3 bg-primary/5 border border-primary/20 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[18px]">fact_check</span>
                        <span class="text-on-surface">
                            Recorte de auditoria ativo:
                            @if (!empty(trim($driverSearch)))
                                Termo <strong>"{{ $driverSearch }}"</strong> •
                            @endif
                            @if ($categoryFilter !== 'todos')
                                Categoria <strong>{{ ucfirst($categoryFilter) }}</strong> •
                            @endif
                            @if ($dayOfWeekFilter !== 'todos')
                                Dia <strong>{{ ucfirst($dayOfWeekFilter) }}</strong> •
                            @endif
                            <strong class="text-primary">{{ $reportStats['total'] }}</strong> registro(s) localizado(s).
                        </span>
                    </div>
                    <button
                        type="button"
                        wire:click="openPreview"
                        class="text-[11px] text-primary hover:underline font-bold cursor-pointer self-start sm:self-auto"
                    >
                        Abrir prévia de impressão →
                    </button>
                </div>
            @endif
        </div>

        <!-- TABELA CONSOLIDADA DO RELATÓRIO -->
        <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs overflow-hidden flex flex-col">
            <div class="p-4 bg-surface-container-low border-b border-surface-container flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[18px]">table_chart</span>
                    <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider">
                        Listagem Auditada de Movimentações
                    </h3>
                </div>
                <span class="text-xs text-on-surface-variant">
                    Exibindo página atual • Use <strong>Visualizar Documento</strong> ou <strong>Gerar PDF</strong> para o relatório completo
                </span>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container/60 text-on-surface-variant uppercase text-[11px] font-bold tracking-wider h-11 border-b border-surface-container">
                            <th class="py-2.5 px-4">Protocolo</th>
                            <th class="py-2.5 px-4">Placa</th>
                            <th class="py-2.5 px-4">Condutor & Vínculo</th>
                            <th class="py-2.5 px-4">Data & Dia da Semana</th>
                            <th class="py-2.5 px-4">Entrada / Saída</th>
                            <th class="py-2.5 px-4">Status & Permanência</th>
                            <th class="py-2.5 px-4">Cancela & Operador</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container text-xs">
                        @forelse ($records as $item)
                            <tr wire:key="report-row-{{ $item['raw_id'] }}" class="hover:bg-surface-container-low/60 transition-colors">
                                <!-- Protocolo -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-md bg-surface-container font-bold text-on-surface font-mono text-[11px]">
                                        {{ $item['id'] }}
                                    </span>
                                </td>

                                <!-- Placa Mercosul -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <x-mercosul-plate :plate="$item['plate']" size="sm" />
                                </td>

                                <!-- Condutor & Categoria -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $badgeStyle = match ($item['category'] ?? 'visitante') {
                                                'professor' => 'bg-primary/10 text-primary',
                                                'funcionario' => 'bg-blue-100 text-blue-800',
                                                'prestador' => 'bg-amber-100 text-amber-800',
                                                default => 'bg-surface-container-high text-on-surface-variant',
                                            };
                                        @endphp
                                        <div class="w-9 h-9 rounded-full {{ $badgeStyle }} flex items-center justify-center font-bold text-xs shrink-0 uppercase">
                                            {{ mb_substr($item['driver'], 0, 2) }}
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-bold text-on-surface text-sm truncate">{{ $item['driver'] }}</span>
                                                @if (($item['occupant_type'] ?? 'condutor') === 'passageiro')
                                                    <span class="px-1.5 py-0.5 rounded bg-purple-100 text-purple-900 text-[10px] font-bold uppercase">
                                                        Carona
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="text-on-surface-variant text-[11px] truncate">
                                                {{ $item['category_label'] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Data & Dia da Semana -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-bold text-on-surface font-mono block">{{ $item['date_only'] }}</span>
                                    <span class="inline-flex mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-surface-container-high text-on-surface-variant">
                                        {{ $item['day_of_week_label'] }}
                                    </span>
                                </td>

                                <!-- Horários de Entrada e Saída -->
                                <td class="py-3.5 px-4 font-mono whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 text-on-surface font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        <span>Ent: {{ $item['entry_hour'] }}</span>
                                    </div>
                                    @if (!empty($item['exit_hour']))
                                        <div class="flex items-center gap-1.5 text-[11px] text-on-surface-variant mt-0.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                            <span>Saí: {{ $item['exit_hour'] }}</span>
                                        </div>
                                    @else
                                        <div class="text-[11px] text-emerald-700 font-sans font-semibold mt-0.5">
                                            Ainda no campus
                                        </div>
                                    @endif
                                </td>

                                <!-- Status & Permanência -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if ($item['event'] === 'entrada')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                                            <span class="material-symbols-outlined text-[14px]">input</span>
                                            <span>{{ $item['event_label'] }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-100 text-amber-900 text-[11px] font-bold">
                                            <span class="material-symbols-outlined text-[14px]">output</span>
                                            <span>{{ $item['event_label'] }}</span>
                                        </span>
                                    @endif

                                    <span class="text-[11px] text-on-surface-variant block mt-1">
                                        Permanência: <strong class="text-on-surface font-semibold">{{ $item['permanencia'] }}</strong>
                                    </span>
                                </td>

                                <!-- Cancela & Operador -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-on-surface">{{ $item['operator'] }}</div>
                                    <div class="text-[11px] text-on-surface-variant">{{ $item['gate'] }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-10 text-center text-on-surface-variant">
                                    <div class="flex flex-col items-center gap-2 max-w-md mx-auto">
                                        <div class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-on-surface-variant/70">
                                            <span class="material-symbols-outlined text-[28px]">manage_search</span>
                                        </div>
                                        <p class="text-sm font-bold text-on-surface">
                                            Nenhum registro de acesso encontrado para os filtros selecionados.
                                        </p>
                                        <p class="text-xs text-on-surface-variant">
                                            As movimentações registradas na portaria e os cadastros de visitantes/condutores aparecerão automaticamente aqui.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- RODAPÉ COM PAGINAÇÃO REAL DO LIVEWIRE -->
            <div class="p-4 bg-surface-container-low border-t border-surface-container flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-on-surface-variant">
                <span>
                    Mostrando <strong class="text-on-surface font-bold">{{ $records->count() }}</strong> de <strong class="text-on-surface font-bold">{{ $records->total() }}</strong> registros filtrados.
                </span>

                @if ($records->hasPages())
                    <div>
                        {{ $records->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- MODAL DE PRÉ-VISUALIZAÇÃO DO DOCUMENTO BASE DO RELATÓRIO -->
        @if ($showPreviewModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                <div class="bg-surface-container-low border border-surface-container rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden">
                    <!-- Topo do Modal -->
                    <div class="px-6 py-4 bg-surface-container-lowest border-b border-surface-container flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-primary text-[22px]">description</span>
                            <div>
                                <h3 class="text-sm font-extrabold text-on-surface">
                                    Pré-visualização do Relatório Oficial (Base de Impressão / PDF)
                                </h3>
                                <p class="text-[11px] text-on-surface-variant">
                                    Verifique a formatação institucional A4 antes de imprimir ou salvar como PDF.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                onclick="window.print()"
                                class="px-4 py-2 bg-primary hover:bg-primary-container text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-xs transition-colors cursor-pointer"
                            >
                                <span class="material-symbols-outlined text-[18px]">print</span>
                                <span>Imprimir / Salvar PDF</span>
                            </button>

                            <button
                                type="button"
                                wire:click="closePreview"
                                class="p-2 rounded-xl text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-colors cursor-pointer"
                                title="Fechar Pré-visualização"
                            >
                                <span class="material-symbols-outlined text-[20px]">close</span>
                            </button>
                        </div>
                    </div>

                    <!-- Corpo do Modal com o Blade Base do Relatório -->
                    <div class="p-6 overflow-y-auto">
                        @include('livewire.admin.partials.relatorio-base')
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- DOCUMENTO BASE EXCLUSIVO PARA IMPRESSÃO / EXPORTAÇÃO PDF -->
    <div class="hidden print-only">
        @include('livewire.admin.partials.relatorio-base')
    </div>
</div>
