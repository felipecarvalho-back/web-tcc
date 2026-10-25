<div class="flex flex-col gap-6">
    <!-- CABEÇALHO DO DASHBOARD -->
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-xs">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded bg-primary-container text-white text-[11px] font-bold uppercase tracking-wider">
                    Administração Central
                </span>
                <span class="text-on-surface-variant text-xs font-semibold uppercase tracking-wider">
                    Gestão Geral de Tráfego & Auditoria
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">
                Painel de Indicadores e Telemetria
            </h1>
            <p class="text-sm text-on-surface-variant">
                Visão consolidada em tempo real de usuários operacionais, veículos cadastrados, fluxo de entrada/saída e registros de passagem.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Seletor Interativo de Período -->
            <div class="inline-flex items-center bg-surface-container-low p-1 rounded-xl border border-surface-container">
                @foreach (['hoje' => 'Hoje', 'ontem' => 'Ontem', '7dias' => '7 Dias', 'mes' => 'Este Mês', 'todos' => 'Geral'] as $key => $label)
                    <button
                        type="button"
                        wire:click="setPeriod('{{ $key }}')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $periodFilter === $key ? 'bg-primary text-white shadow-2xs' : 'text-on-surface-variant hover:text-on-surface' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <a
                href="{{ route('admin.relatorios') }}"
                wire:navigate
                class="px-4 py-2.5 bg-primary hover:bg-primary-container text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-xs transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                <span>Gerar Relatório</span>
            </a>
        </div>
    </div>

    <!-- OS 4 INDICADORES CENTRAIS CONECTADOS AO BANCO -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. USUÁRIOS ATIVOS NO SISTEMA -->
        <div class="p-5 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                    Usuários Ativos no Sistema
                </span>
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[22px]">manage_accounts</span>
                </div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-on-surface font-mono">{{ $metrics['active_users'] }}</div>
                <div class="flex items-center gap-1.5 mt-1 text-xs text-emerald-700 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>
                        {{ $metrics['operators_count'] }} operador(es) + {{ $metrics['admins_count'] }} admin(s)
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. QUANTIDADE DE VEÍCULOS CADASTRADOS -->
        <div class="p-5 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                    Quantidade de Veículos
                </span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-700">
                    <span class="material-symbols-outlined text-[22px]">directions_car</span>
                </div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-on-surface font-mono">
                    {{ number_format($metrics['total_vehicles'], 0, ',', '.') }}
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-xs text-on-surface-variant">
                    <span>Vinculados a <strong class="text-on-surface font-bold">{{ $metrics['active_drivers'] }}</strong> condutor(es) ativo(s)</span>
                </div>
            </div>
        </div>

        <!-- 3. NÚMERO DE ENTRADAS E SAÍDAS NO PERÍODO -->
        <div class="p-5 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                    Entradas & Saídas
                </span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-700">
                    <span class="material-symbols-outlined text-[22px]">swap_vert</span>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-3">
                    <div>
                        <span class="text-[10px] font-bold text-emerald-700 uppercase block">Entradas</span>
                        <span class="text-2xl font-extrabold text-emerald-700 font-mono">{{ $metrics['entries_count'] }}</span>
                    </div>
                    <span class="text-lg text-on-surface-variant font-light">/</span>
                    <div>
                        <span class="text-[10px] font-bold text-amber-700 uppercase block">Saídas</span>
                        <span class="text-2xl font-extrabold text-amber-700 font-mono">{{ $metrics['exits_count'] }}</span>
                    </div>
                </div>
                <div class="text-[11px] text-on-surface-variant mt-1">
                    No pátio agora: <strong class="text-on-surface font-bold font-mono">{{ $metrics['in_campus_count'] }} veículo(s)</strong>
                </div>
            </div>
        </div>

        <!-- 4. NÚMERO DE REGISTROS DE PASSAGEM -->
        <div class="p-5 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                    Total de Registros
                </span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-700">
                    <span class="material-symbols-outlined text-[22px]">receipt_long</span>
                </div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-on-surface font-mono">
                    {{ number_format($metrics['total_passage_records'], 0, ',', '.') }}
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-xs text-on-surface-variant">
                    <span><strong class="text-on-surface font-bold">{{ $metrics['manual_releases_count'] }}</strong> liberação(ões) manual(is) no período</span>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICO DE FLUXO HORÁRIO DE TRÁFEGO (DADOS REAIS DO BANCO) -->
    <div class="bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-4">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]">show_chart</span>
                <div>
                    <h2 class="text-base font-bold text-on-surface">Curva de Tráfego Horário (07h às 22h)</h2>
                    <p class="text-xs text-on-surface-variant">Distribuição real de entradas e saídas registradas nas cancelas</p>
                </div>
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <span class="flex items-center gap-1.5 text-emerald-700">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                    Entradas ({{ $metrics['entries_count'] }})
                </span>
                <span class="flex items-center gap-1.5 text-amber-700">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-600"></span>
                    Saídas ({{ $metrics['exits_count'] }})
                </span>
            </div>
        </div>

        <!-- Gráfico em barras CSS Responsivas -->
        <div class="h-56 flex items-end justify-between gap-2 pt-6 px-2">
            @foreach ($hourlyTraffic as $h)
                <div wire:key="hour-{{ $h['time'] }}" class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end group">
                    <div class="w-full flex items-end justify-center gap-1 h-full">
                        <!-- Barra Entrada -->
                        <div
                            style="height: {{ $h['in_pct'] }}%;"
                            class="w-1/2 max-w-[16px] {{ $h['in'] > 0 ? 'bg-emerald-600 group-hover:bg-emerald-500' : 'bg-surface-container-high' }} rounded-t-sm transition-all relative"
                            title="{{ $h['in'] }} Entrada(s) às {{ $h['time'] }}"
                        ></div>
                        <!-- Barra Saída -->
                        <div
                            style="height: {{ $h['out_pct'] }}%;"
                            class="w-1/2 max-w-[16px] {{ $h['out'] > 0 ? 'bg-amber-600 group-hover:bg-amber-500' : 'bg-surface-container-high' }} rounded-t-sm transition-all relative"
                            title="{{ $h['out'] }} Saída(s) às {{ $h['time'] }}"
                        ></div>
                    </div>
                    <span class="text-[10px] text-on-surface-variant font-mono font-semibold">{{ $h['time'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="p-3.5 bg-surface-container-low rounded-xl border border-surface-container/60 flex items-center justify-between text-xs text-on-surface-variant flex-wrap gap-2">
            <span>
                Faixa de maior movimento no período: <strong class="text-on-surface font-bold">{{ $metrics['peak_hour_label'] }}</strong>
            </span>
            <span class="font-bold text-primary">
                Placas Identificadas no Período: {{ $metrics['identified_plates_count'] }} de {{ $metrics['entries_count'] }}
            </span>
        </div>
    </div>

    <!-- TABELA DE AUDITORIA DE LIBERAÇÕES RECENTES -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs overflow-hidden flex flex-col">
        <div class="p-5 bg-surface-container-low border-b border-surface-container flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]">security</span>
                <div>
                    <h3 class="text-base font-bold text-on-surface">Auditoria de Liberações Recentes</h3>
                    <p class="text-xs text-on-surface-variant">Últimas passagens e intervenções operacionais registradas na portaria</p>
                </div>
            </div>
            <a
                href="{{ route('admin.relatorios') }}"
                wire:navigate
                class="text-xs font-bold text-primary hover:underline flex items-center gap-1"
            >
                <span>Ver Relatório Completo</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container/60 text-on-surface-variant uppercase text-[11px] font-bold tracking-wider h-11 border-b border-surface-container">
                        <th class="py-2.5 px-4">Protocolo</th>
                        <th class="py-2.5 px-4">Placa</th>
                        <th class="py-2.5 px-4">Condutor & Vínculo</th>
                        <th class="py-2.5 px-4">Ação Realizada</th>
                        <th class="py-2.5 px-4">Operador / Agente</th>
                        <th class="py-2.5 px-4">Cancela & Horário</th>
                        <th class="py-2.5 px-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container text-xs">
                    @forelse ($recentAuditLogs as $log)
                        <tr wire:key="audit-log-{{ $log['raw_id'] }}" class="hover:bg-surface-container-low/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-on-surface whitespace-nowrap">
                                <span class="px-2 py-1 rounded-md bg-surface-container text-[11px]">
                                    {{ $log['id'] }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <x-mercosul-plate :plate="$log['plate']" size="sm" />
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="font-bold text-on-surface text-sm">{{ $log['driver'] }}</div>
                                <div class="text-[11px] text-on-surface-variant">{{ $log['role'] }}</div>
                            </td>

                            <td class="py-3.5 px-4 text-on-surface font-medium">
                                {{ $log['action'] }}
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-on-surface">{{ $log['operator'] }}</div>
                                @if (!empty($log['operator_code']))
                                    <span class="text-[10px] font-mono text-on-surface-variant">{{ $log['operator_code'] }}</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-mono font-bold text-on-surface">
                                    {{ $log['time'] }}
                                    @if (!empty($log['exit_time']))
                                        <span class="text-on-surface-variant font-normal">→ {{ $log['exit_time'] }}</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-on-surface-variant">{{ $log['gate'] }}</div>
                            </td>

                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                @php
                                    $statusClasses = match ($log['status_state'] ?? 'autorizado') {
                                        'saida' => 'bg-surface-container-high text-on-surface-variant',
                                        'nao_cadastrado' => 'bg-error-container text-on-error-container',
                                        'pendente' => 'bg-amber-100 text-amber-900',
                                        default => 'bg-emerald-100 text-emerald-800',
                                    };
                                @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-full {{ $statusClasses }} text-[11px] font-bold">
                                    {{ $log['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center text-on-surface-variant">
                                <div class="flex flex-col items-center gap-2 max-w-md mx-auto">
                                    <div class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-on-surface-variant/70">
                                        <span class="material-symbols-outlined text-[28px]">analytics</span>
                                    </div>
                                    <p class="text-sm font-bold text-on-surface">
                                        Nenhuma movimentação registrada neste período.
                                    </p>
                                    <p class="text-xs text-on-surface-variant">
                                        Assim que veículos passarem pela cancela ou visitantes forem liberados na guarita, os indicadores e logs aparecerão aqui em tempo real.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
