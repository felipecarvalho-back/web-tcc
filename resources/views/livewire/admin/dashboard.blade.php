<div class="flex flex-col gap-6">
    <!-- CABEÇALHO DO DASHBOARD -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-xs">
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
                Visão consolidada de usuários operacionais, veículos cadastrados, fluxo de entrada/saída e registros de passagem.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3.5 py-2 bg-surface-container-low rounded-xl border border-surface-container text-on-surface text-xs font-bold">
                <span class="material-symbols-outlined text-[18px] text-primary">calendar_month</span>
                <span>Período: Hoje (Tempo Real)</span>
            </div>

            <a 
                href="{{ route('admin.relatorios') }}"
                class="px-4 py-2 bg-primary hover:bg-primary-container text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-xs transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                <span>Gerar Relatório</span>
            </a>
        </div>
    </div>

    <!-- OS 4 INDICADORES CENTRAIS REQUISITADOS -->
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
                <div class="text-3xl font-extrabold text-on-surface font-mono">{{ $activeUsers }}</div>
                <div class="flex items-center gap-1.5 mt-1 text-xs text-emerald-600 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>3 operadores em guaritas + 5 admins</span>
                </div>
            </div>
        </div>

        <!-- 2. QUANTIDADE DE VEÍCULOS -->
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
                <div class="text-3xl font-extrabold text-on-surface font-mono">{{ number_format($totalVehicles, 0, ',', '.') }}</div>
                <div class="flex items-center gap-1.5 mt-1 text-xs text-on-surface-variant">
                    <span>Professores (N carros), servidores & prestadores</span>
                </div>
            </div>
        </div>

        <!-- 3. NÚMERO DE SAÍDA E ENTRADA DE VEÍCULOS -->
        <div class="p-5 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                    Entradas & Saídas (Hoje)
                </span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-700">
                    <span class="material-symbols-outlined text-[22px]">swap_vert</span>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-3">
                    <div>
                        <span class="text-[10px] font-bold text-emerald-700 uppercase block">Entradas</span>
                        <span class="text-2xl font-extrabold text-emerald-700 font-mono">{{ $entriesCount }}</span>
                    </div>
                    <span class="text-lg text-on-surface-variant font-light">/</span>
                    <div>
                        <span class="text-[10px] font-bold text-amber-700 uppercase block">Saídas</span>
                        <span class="text-2xl font-extrabold text-amber-700 font-mono">{{ $exitsCount }}</span>
                    </div>
                </div>
                <div class="text-[11px] text-on-surface-variant mt-1">
                    Saldo no campus: <strong class="text-on-surface font-bold font-mono">{{ $entriesCount - $exitsCount }} veículos</strong>
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
                <div class="text-3xl font-extrabold text-on-surface font-mono">{{ number_format($totalPassageRecords, 0, ',', '.') }}</div>
                <div class="flex items-center gap-1.5 mt-1 text-xs text-on-surface-variant">
                    <span>Passagens registradas pelo sistema</span>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICO DE FLUXO HORÁRIO DE TRÁFEGO (FULL WIDTH) -->
    <div class="bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-4">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]">show_chart</span>
                <h2 class="text-base font-bold text-on-surface">Curva de Tráfego Horário (07h às 22h)</h2>
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <span class="flex items-center gap-1.5 text-emerald-700">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                    Entradas
                </span>
                <span class="flex items-center gap-1.5 text-amber-700">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-600"></span>
                    Saídas
                </span>
            </div>
        </div>

        <!-- Gráfico em barras CSS Responsivas -->
        <div class="h-60 flex items-end justify-between gap-2 pt-6 px-2">
            @php
                $hours = [
                    ['time' => '07h', 'in' => 65, 'out' => 8],
                    ['time' => '08h', 'in' => 98, 'out' => 14],
                    ['time' => '09h', 'in' => 45, 'out' => 20],
                    ['time' => '11h', 'in' => 30, 'out' => 40],
                    ['time' => '12h', 'in' => 52, 'out' => 88],
                    ['time' => '13h', 'in' => 78, 'out' => 35],
                    ['time' => '15h', 'in' => 25, 'out' => 30],
                    ['time' => '17h', 'in' => 35, 'out' => 95],
                    ['time' => '18h', 'in' => 85, 'out' => 45],
                    ['time' => '19h', 'in' => 90, 'out' => 25],
                    ['time' => '21h', 'in' => 12, 'out' => 85],
                    ['time' => '22h', 'in' => 5,  'out' => 98],
                ];
            @endphp

            @foreach ($hours as $h)
                <div class="flex-1 flex flex-col items-center gap-1 h-full justify-end group">
                    <div class="w-full flex items-end justify-center gap-1.5 h-full">
                        <!-- Barra Entrada -->
                        <div 
                            style="height: {{ ($h['in'] / 100) * 85 }}%;"
                            class="w-1/2 max-w-[16px] bg-emerald-600 rounded-t-sm group-hover:bg-emerald-500 transition-all relative"
                            title="{{ $h['in'] }} Entradas às {{ $h['time'] }}"
                        ></div>
                        <!-- Barra Saída -->
                        <div 
                            style="height: {{ ($h['out'] / 100) * 85 }}%;"
                            class="w-1/2 max-w-[16px] bg-amber-600 rounded-t-sm group-hover:bg-amber-500 transition-all relative"
                            title="{{ $h['out'] }} Saídas às {{ $h['time'] }}"
                        ></div>
                    </div>
                    <span class="text-[10px] text-on-surface-variant font-mono">{{ $h['time'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="p-3 bg-surface-container-low rounded-xl border border-surface-container/60 flex items-center justify-between text-xs text-on-surface-variant flex-wrap gap-2">
            <span>Horários de Maior Pico: <strong>07:30 - 08:30</strong> (Abertura Aulas) e <strong>18:30 - 19:15</strong> (Turno Noturno).</span>
            <span class="font-bold text-primary">Tempo Médio de Liberação: 1.8s</span>
        </div>
    </div>

    <!-- TABELA DE AUDITORIA RECENTE -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs overflow-hidden flex flex-col">
        <div class="p-5 bg-surface-container-low border-b border-surface-container flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]">security</span>
                <h3 class="text-base font-bold text-on-surface">Auditoria de Liberações Recentes</h3>
            </div>
            <a href="{{ route('admin.relatorios') }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                <span>Ver Log Completo</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container/60 text-on-surface-variant uppercase text-[11px] font-bold tracking-wider h-10 border-b border-surface-container">
                        <th class="py-2.5 px-4">Operador / Agente</th>
                        <th class="py-2.5 px-4">Ação Realizada</th>
                        <th class="py-2.5 px-4">Placa</th>
                        <th class="py-2.5 px-4">Condutor / Vínculo</th>
                        <th class="py-2.5 px-4">Cancela / Portaria</th>
                        <th class="py-2.5 px-4">Horário</th>
                        <th class="py-2.5 px-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container text-xs">
                    @foreach ($recentAuditLogs as $log)
                        <tr class="hover:bg-surface-container-low/60 transition-colors">
                            <td class="py-3 px-4 font-bold text-on-surface">{{ $log['operator'] }}</td>
                            <td class="py-3 px-4 text-on-surface-variant">{{ $log['action'] }}</td>
                            <td class="py-3 px-4 font-bold font-mono text-on-surface">{{ $log['plate'] }}</td>
                            <td class="py-3 px-4 text-on-surface-variant">{{ $log['role'] }}</td>
                            <td class="py-3 px-4 text-on-surface-variant">{{ $log['gate'] }}</td>
                            <td class="py-3 px-4 font-mono text-on-surface">{{ $log['time'] }}</td>
                            <td class="py-3 px-4 text-right">
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                    {{ $log['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
