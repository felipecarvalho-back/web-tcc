<div class="flex flex-col gap-6">
    <!-- ÁREA INTERATIVA WEB (OCULTA NA IMPRESSÃO) -->
    <div class="flex flex-col gap-6 no-print">
        <!-- CABEÇALHO DE RELATÓRIOS -->
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-5 bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-xs">
            <div class="flex flex-col gap-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-primary/10 text-primary text-[11px] font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                        Auditoria & Tráfego
                    </span>
                    <span class="text-on-surface-variant text-xs font-semibold tracking-wider">
                        • Exportação em PDF & CSV
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">
                    Relatórios Gerenciais de Tráfego
                </h1>
                <p class="text-sm text-on-surface-variant max-w-3xl">
                    Auditoria analítica de entradas, saídas, permanência no campus e conferência de assiduidade docente.
                </p>
            </div>

            <!-- BOTÕES DE AÇÃO E EXPORTAÇÃO -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0">
                <button
                    type="button"
                    wire:click="openPreview"
                    class="px-4 py-2.5 bg-surface-container-low hover:bg-surface-container text-on-surface text-xs font-bold rounded-xl flex items-center justify-center gap-2 border border-surface-container-highest transition-colors cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[19px] text-primary">visibility</span>
                    <span>Visualizar Documento</span>
                </button>

                <button
                    type="button"
                    wire:click="exportCsv"
                    wire:loading.attr="disabled"
                    class="px-4 py-2.5 bg-surface-container-low hover:bg-surface-container text-on-surface text-xs font-bold rounded-xl flex items-center justify-center gap-2 border border-surface-container-highest transition-colors cursor-pointer disabled:opacity-50"
                >
                    <span class="material-symbols-outlined text-[19px] text-emerald-700">table_view</span>
                    <span wire:loading.remove wire:target="exportCsv">Exportar Planilha (CSV)</span>
                    <span wire:loading wire:target="exportCsv">Gerando CSV...</span>
                </button>

                <button
                    type="button"
                    onclick="window.print()"
                    class="px-5 py-2.5 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl flex items-center justify-center gap-2 shadow-xs hover:shadow-md transition-all cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[19px]">picture_as_pdf</span>
                    <span>Gerar Relatório em PDF</span>
                </button>
            </div>
        </div>

        <!-- 4 INDICADORES EXECUTIVOS DO PERÍODO FILTRADO (DESIGN LIMPO & RESPONSIVO) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Total Filtrado -->
            <div class="p-4 sm:p-5 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3 transition-all hover:border-surface-container-highest">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] sm:text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Registros Filtrados
                    </span>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-surface-container flex items-center justify-center text-on-surface shrink-0">
                        <span class="material-symbols-outlined text-[19px] sm:text-[21px]">receipt_long</span>
                    </div>
                </div>
                <div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-on-surface font-mono tracking-tight">{{ $reportStats['total'] }}</div>
                    <span class="text-[11px] text-on-surface-variant mt-0.5 block truncate">
                        Passagens na consulta
                    </span>
                </div>
            </div>

            <!-- Entradas Ativas / No Pátio -->
            <div class="p-4 sm:p-5 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3 transition-all hover:border-surface-container-highest">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] sm:text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Em Pátio (Entradas)
                    </span>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-700 shrink-0">
                        <span class="material-symbols-outlined text-[19px] sm:text-[21px]">input</span>
                    </div>
                </div>
                <div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-emerald-700 font-mono tracking-tight">{{ $reportStats['entradas'] }}</div>
                    <span class="text-[11px] text-on-surface-variant mt-0.5 block truncate">
                        Sem baixa de saída
                    </span>
                </div>
            </div>

            <!-- Saídas Concluídas -->
            <div class="p-4 sm:p-5 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3 transition-all hover:border-surface-container-highest">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] sm:text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Saídas Concluídas
                    </span>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-700 shrink-0">
                        <span class="material-symbols-outlined text-[19px] sm:text-[21px]">output</span>
                    </div>
                </div>
                <div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-amber-700 font-mono tracking-tight">{{ $reportStats['saidas'] }}</div>
                    <span class="text-[11px] text-on-surface-variant mt-0.5 block truncate">
                        Ciclos finalizados
                    </span>
                </div>
            </div>

            <!-- Docentes Identificados -->
            <div class="p-4 sm:p-5 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3 transition-all hover:border-surface-container-highest">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] sm:text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Acessos Docentes
                    </span>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined text-[19px] sm:text-[21px]">school</span>
                    </div>
                </div>
                <div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-primary font-mono tracking-tight">{{ $reportStats['docentes'] }}</div>
                    <span class="text-[11px] text-on-surface-variant mt-0.5 block truncate">
                        Professores no período
                    </span>
                </div>
            </div>
        </div>

        <!-- PAINEL DE FILTROS (DESIGN LIMPO, INTUITIVO E RESPONSIVO) -->
        <div class="bg-surface-container-lowest p-5 sm:p-6 rounded-2xl border border-surface-container shadow-xs flex flex-col gap-5">
            <!-- Barra superior dos filtros -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between pb-4 border-b border-surface-container gap-3.5">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">tune</span>
                    <h2 class="text-xs font-bold text-on-surface uppercase tracking-wider">
                        Filtros de Tráfego e Assiduidade Docente
                    </h2>
                </div>

                <!-- Atalhos de Período + Botão Limpar -->
                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center bg-surface-container-low p-1 rounded-xl border border-surface-container max-w-full overflow-x-auto no-scrollbar">
                        @foreach (['hoje' => 'Hoje', '7dias' => 'Últimos 7 dias', 'mes' => 'Este Mês', 'ano' => 'Ano Atual'] as $key => $label)
                            <button
                                type="button"
                                wire:click="setQuickPeriod('{{ $key }}')"
                                class="whitespace-nowrap px-3 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer {{ $quickPeriod === $key ? 'bg-primary text-white shadow-2xs' : 'text-on-surface-variant hover:text-on-surface' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    @if ($dayOfWeekFilter !== 'todos' || !empty(trim($driverSearch)) || $categoryFilter !== 'todos' || $eventType !== 'todos' || $operatorFilter !== 'todos')
                        <button
                            type="button"
                            wire:click="clearAssiduidadeFilters"
                            class="px-3 py-1.5 rounded-xl bg-error/10 hover:bg-error/20 text-error text-xs font-bold flex items-center gap-1 transition-colors cursor-pointer shrink-0"
                            title="Restaurar filtros padrão"
                        >
                            <span class="material-symbols-outlined text-[16px]">filter_alt_off</span>
                            <span>Limpar Filtros</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Linha 1: Busca Geral + Intervalo de Datas -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5">
                <!-- Buscar Professor / Condutor / Placa (6 colunas) -->
                <div class="md:col-span-6 flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-primary text-[15px]">person_search</span>
                        <span>Buscar por Condutor, Professor, Placa ou CPF</span>
                    </label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[18px] pointer-events-none">search</span>
                        <input
                            wire:model.live.debounce.300ms="driverSearch"
                            type="text"
                            placeholder="Nome do condutor, placa Mercosul ou CPF..."
                            class="h-10 pl-9 pr-8 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary w-full transition-all"
                        />
                        @if (!empty($driverSearch))
                            <button
                                type="button"
                                wire:click="$set('driverSearch', '')"
                                class="absolute right-2.5 text-on-surface-variant hover:text-on-surface cursor-pointer p-0.5 rounded-md hover:bg-surface-container"
                                title="Limpar busca"
                            >
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Data Inicial (3 colunas) -->
                <div class="md:col-span-3 flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-primary text-[15px]">calendar_today</span>
                        <span>Data Inicial</span>
                    </label>
                    <input
                        wire:model.live="startDate"
                        type="date"
                        class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary w-full transition-all"
                    />
                </div>

                <!-- Data Final (3 colunas) -->
                <div class="md:col-span-3 flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-primary text-[15px]">event</span>
                        <span>Data Final</span>
                    </label>
                    <input
                        wire:model.live="endDate"
                        type="date"
                        class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary w-full transition-all"
                    />
                </div>
            </div>

            <!-- Linha 2: Refinamentos Específicos -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                <!-- Dia da Semana (Frequência) -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-primary text-[15px]">event_repeat</span>
                        <span>Dia da Semana</span>
                    </label>
                    <select
                        wire:model.live="dayOfWeekFilter"
                        class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer w-full transition-all"
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

                <!-- Categoria de Vínculo -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-primary text-[15px]">badge</span>
                        <span>Categoria de Vínculo</span>
                    </label>
                    <select
                        wire:model.live="categoryFilter"
                        class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer w-full transition-all"
                    >
                        <option value="todos">Todas as Categorias</option>
                        <option value="professor">Professores (Docentes)</option>
                        <option value="funcionario">Funcionários / Servidores</option>
                        <option value="prestador">Prestadores de Serviço</option>
                        <option value="visitante">Visitantes</option>
                    </select>
                </div>

                <!-- Status da Movimentação -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-primary text-[15px]">swap_horiz</span>
                        <span>Status da Movimentação</span>
                    </label>
                    <select
                        wire:model.live="eventType"
                        class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer w-full transition-all"
                    >
                        <option value="todos">Todos os Eventos</option>
                        <option value="entrada">Apenas Entradas (Em Pátio)</option>
                        <option value="saida">Apenas Saídas Concluídas</option>
                    </select>
                </div>

                <!-- Operador da Guarita -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-primary text-[15px]">security</span>
                        <span>Operador Responsável</span>
                    </label>
                    <select
                        wire:model.live="operatorFilter"
                        class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer w-full transition-all"
                    >
                        <option value="todos">Todos os Operadores</option>
                        @foreach ($operators as $op)
                            <option value="{{ $op['nome'] }}">{{ $op['nome'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Banner de Feedback Limpo quando filtros estão ativos -->
            @if ($dayOfWeekFilter !== 'todos' || !empty(trim($driverSearch)) || $categoryFilter !== 'todos' || $eventType !== 'todos' || $operatorFilter !== 'todos')
                <div class="p-3 bg-primary/5 border border-primary/20 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="material-symbols-outlined text-primary text-[18px] shrink-0">fact_check</span>
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
                            @if ($eventType !== 'todos')
                                Evento <strong>{{ $eventType === 'entrada' ? 'Entradas (Em Pátio)' : 'Saídas' }}</strong> •
                            @endif
                            @if ($operatorFilter !== 'todos')
                                Operador <strong>{{ $operatorFilter }}</strong> •
                            @endif
                            <strong class="text-primary">{{ $reportStats['total'] }}</strong> registro(s) localizado(s).
                        </span>
                    </div>
                    <button
                        type="button"
                        wire:click="openPreview"
                        class="text-[11px] text-primary hover:underline font-bold cursor-pointer inline-flex items-center gap-1 self-start sm:self-auto shrink-0"
                    >
                        <span>Abrir prévia</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </button>
                </div>
            @endif
        </div>

        <!-- LISTAGEM CONSOLIDADA DO RELATÓRIO (TABELA DESKTOP + CARDS MOBILE) -->
        <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs overflow-hidden flex flex-col">
            <!-- Barra superior da listagem -->
            <div class="px-5 py-4 bg-surface-container-low border-b border-surface-container flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[19px]">table_chart</span>
                    <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider">
                        Listagem Auditada de Movimentações
                    </h3>
                </div>
                <span class="text-xs text-on-surface-variant">
                    Exibindo página atual • Use <strong>Visualizar Documento</strong> ou <strong>Gerar PDF</strong> para o relatório completo
                </span>
            </div>

            <!-- 1. TABELA DESKTOP / TABLET (VISÍVEL A PARTIR DE MD) -->
            <div class="hidden md:block overflow-x-auto w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container/50 text-on-surface-variant uppercase text-[11px] font-bold tracking-wider h-11 border-b border-surface-container">
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
                            <tr wire:key="report-row-desktop-{{ $item['raw_id'] }}" class="hover:bg-surface-container-low/60 transition-colors">
                                <!-- Protocolo -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-md bg-surface-container font-bold text-on-surface font-mono text-[11px]">
                                        {{ $item['id'] }}
                                    </span>
                                </td>

                                <!-- Placa Mercosul -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <x-mercosul-plate :plate="$item['plate']" size="sm" />
                                </td>

                                <!-- Condutor & Categoria -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2.5">
                                        @php
                                            $badgeStyle = match ($item['category'] ?? 'visitante') {
                                                'professor' => 'bg-primary/10 text-primary border-primary/20',
                                                'funcionario' => 'bg-blue-500/10 text-blue-800 border-blue-500/20',
                                                'prestador' => 'bg-amber-500/10 text-amber-800 border-amber-500/20',
                                                default => 'bg-surface-container text-on-surface-variant border-surface-container-highest',
                                            };
                                        @endphp
                                        <div class="w-7 h-7 rounded-lg {{ $badgeStyle }} border flex items-center justify-center font-bold text-[11px] shrink-0 uppercase">
                                            {{ mb_substr($item['driver'], 0, 2) }}
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-bold text-on-surface text-sm truncate">{{ $item['driver'] }}</span>
                                                @if (($item['occupant_type'] ?? 'condutor') === 'passageiro')
                                                    <span class="px-1.5 py-0.2 rounded bg-purple-100 text-purple-900 text-[10px] font-bold uppercase">
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
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-bold text-on-surface font-mono block">{{ $item['date_only'] }}</span>
                                    <span class="inline-flex mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-surface-container text-on-surface-variant">
                                        {{ $item['day_of_week_label'] }}
                                    </span>
                                </td>

                                <!-- Horários de Entrada e Saída -->
                                <td class="py-3 px-4 font-mono whitespace-nowrap">
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
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if ($item['event'] === 'entrada')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                                            <span class="material-symbols-outlined text-[13px]">input</span>
                                            <span>{{ $item['event_label'] }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[11px] font-bold">
                                            <span class="material-symbols-outlined text-[13px]">output</span>
                                            <span>{{ $item['event_label'] }}</span>
                                        </span>
                                    @endif

                                    <span class="text-[11px] text-on-surface-variant block mt-1">
                                        Permanência: <strong class="text-on-surface font-semibold">{{ $item['permanencia'] }}</strong>
                                    </span>
                                </td>

                                <!-- Cancela & Operador -->
                                <td class="py-3 px-4 whitespace-nowrap">
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

            <!-- 2. CARDS MOBILE (VISÍVEL ABAIXO DE MD PARA RESPONSIVIDADE PERFEITA NO CELULAR) -->
            <div class="block md:hidden divide-y divide-surface-container">
                @forelse ($records as $item)
                    <div wire:key="report-card-mobile-{{ $item['raw_id'] }}" class="p-4 flex flex-col gap-3 hover:bg-surface-container-low/40 transition-colors">
                        <!-- Topo: Protocolo, Placa e Status -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-surface-container font-bold text-on-surface font-mono text-[11px]">
                                    {{ $item['id'] }}
                                </span>
                                <x-mercosul-plate :plate="$item['plate']" size="sm" />
                            </div>

                            @if ($item['event'] === 'entrada')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                                    <span class="material-symbols-outlined text-[13px]">input</span>
                                    <span>{{ $item['event_label'] }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[11px] font-bold">
                                    <span class="material-symbols-outlined text-[13px]">output</span>
                                    <span>{{ $item['event_label'] }}</span>
                                </span>
                            @endif
                        </div>

                        <!-- Condutor e Categoria -->
                        <div class="flex flex-col gap-0.5">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-extrabold text-on-surface text-sm">{{ $item['driver'] }}</span>
                                @if (($item['occupant_type'] ?? 'condutor') === 'passageiro')
                                    <span class="px-1.5 py-0.2 rounded bg-purple-100 text-purple-900 text-[10px] font-bold uppercase">
                                        Carona
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs text-on-surface-variant font-medium">
                                {{ $item['category_label'] }}
                            </span>
                        </div>

                        <!-- Grid de Informações Detalhadas (2 colunas) -->
                        <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-surface-container/60">
                            <!-- Coluna 1: Data e Dia -->
                            <div>
                                <span class="text-[10px] uppercase font-bold text-on-surface-variant block">Data / Frequência</span>
                                <span class="font-bold text-on-surface font-mono">{{ $item['date_only'] }}</span>
                                <span class="text-[11px] text-on-surface-variant block">{{ $item['day_of_week_label'] }}</span>
                            </div>

                            <!-- Coluna 2: Horários -->
                            <div class="font-mono">
                                <span class="text-[10px] uppercase font-bold text-on-surface-variant block">Horários</span>
                                <div class="text-on-surface font-semibold flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    <span>Ent: {{ $item['entry_hour'] }}</span>
                                </div>
                                @if (!empty($item['exit_hour']))
                                    <div class="text-on-surface-variant text-[11px] flex items-center gap-1 mt-0.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                        <span>Saí: {{ $item['exit_hour'] }}</span>
                                    </div>
                                @else
                                    <span class="text-[11px] text-emerald-700 font-sans font-semibold">Em Pátio</span>
                                @endif
                            </div>

                            <!-- Coluna 3: Permanência -->
                            <div>
                                <span class="text-[10px] uppercase font-bold text-on-surface-variant block">Permanência</span>
                                <span class="font-semibold text-on-surface">{{ $item['permanencia'] }}</span>
                            </div>

                            <!-- Coluna 4: Operador & Cancela -->
                            <div>
                                <span class="text-[10px] uppercase font-bold text-on-surface-variant block">Guarita</span>
                                <span class="font-semibold text-on-surface block truncate">{{ $item['operator'] }}</span>
                                <span class="text-[11px] text-on-surface-variant block truncate">{{ $item['gate'] }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-on-surface-variant flex flex-col items-center gap-2">
                        <div class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-on-surface-variant/70">
                            <span class="material-symbols-outlined text-[28px]">manage_search</span>
                        </div>
                        <p class="text-sm font-bold text-on-surface">
                            Nenhum registro de acesso encontrado.
                        </p>
                        <p class="text-xs text-on-surface-variant">
                            Ajuste os filtros de pesquisa ou limpe a consulta para ver mais movimentações.
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- RODAPÉ COM PAGINAÇÃO REAL DO LIVEWIRE (RESPONSIVO) -->
            <div class="px-5 py-4 bg-surface-container-low border-t border-surface-container flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-on-surface-variant">
                <span>
                    Mostrando <strong class="text-on-surface font-bold">{{ $records->count() }}</strong> de <strong class="text-on-surface font-bold">{{ $records->total() }}</strong> registros filtrados.
                </span>

                @if ($records->hasPages())
                    <div class="self-center sm:self-auto overflow-x-auto max-w-full">
                        {{ $records->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- MODAL DE PRÉ-VISUALIZAÇÃO DO DOCUMENTO BASE DO RELATÓRIO (RESPONSIVO) -->
        @if ($showPreviewModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-black/60 backdrop-blur-xs">
                <div class="bg-surface-container-lowest border border-surface-container rounded-2xl shadow-2xl w-full max-w-5xl max-h-[92vh] flex flex-col overflow-hidden">
                    <!-- Topo do Modal -->
                    <div class="px-5 py-4 bg-surface-container-lowest border-b border-surface-container flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                <span class="material-symbols-outlined text-[20px]">description</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-on-surface">
                                    Pré-visualização do Relatório Oficial (Base de Impressão / PDF)
                                </h3>
                                <p class="text-[11px] text-on-surface-variant">
                                    Verifique a formatação institucional A4 antes de imprimir ou salvar como PDF.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-auto">
                            <button
                                type="button"
                                onclick="window.print()"
                                class="px-3.5 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-xs transition-colors cursor-pointer"
                            >
                                <span class="material-symbols-outlined text-[17px]">print</span>
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
                    <div class="p-3 sm:p-6 overflow-y-auto overflow-x-hidden bg-surface-container-low">
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
