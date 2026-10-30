@php
    $diasNomes = [
        'todos' => 'Todos os dias',
        'segunda' => 'Segunda-feira',
        'terca' => 'Terça-feira',
        'quarta' => 'Quarta-feira',
        'quinta' => 'Quinta-feira',
        'sexta' => 'Sexta-feira',
        'sabado' => 'Sábado',
        'domingo' => 'Domingo',
    ];

    $categoriasNomes = [
        'todos' => 'Todas as categorias',
        'professor' => 'Docentes (Professores)',
        'funcionario' => 'Servidores / Funcionários',
        'prestador' => 'Prestadores de Serviço',
        'visitante' => 'Visitantes / Não Cadastrados',
    ];

    $periodoInicioFormatado = !empty($startDate) ? \Illuminate\Support\Carbon::parse($startDate)->format('d/m/Y') : '--/--/----';
    $periodoFimFormatado = !empty($endDate) ? \Illuminate\Support\Carbon::parse($endDate)->format('d/m/Y') : '--/--/----';
@endphp

<div class="bg-white text-gray-900 p-5 sm:p-7 rounded-xl border border-gray-300 shadow-xs print:border-0 print:shadow-none print:p-0 text-left">
    <!-- CABEÇALHO INSTITUCIONAL SIMPLES & TOTALMENTE ALINHADO À ESQUERDA -->
    <div class="pb-3 border-b border-gray-900 text-left">
        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500 block">
            Governo do Estado de São Paulo • Centro Paula Souza
        </span>
        <h2 class="text-lg sm:text-xl font-black tracking-tight text-gray-900 uppercase mt-0.5">
            Relatório Oficial de Tráfego e Assiduidade
        </h2>
        <span class="text-xs text-gray-600 block mt-0.5">
            Sentinela FATEC • Sistema Integrado de Monitoramento e Portaria
        </span>

        <!-- DADOS DE EMISSÃO, PERÍODO E EMITIDO POR - ALINHADOS À ESQUERDA -->
        <div class="mt-3 pt-2.5 border-t border-gray-200 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs text-gray-700 text-left">
            <div>
                <span class="text-gray-500">Emissão:</span>
                <strong class="text-gray-900 font-mono ml-1">{{ \Illuminate\Support\Carbon::now()->format('d/m/Y H:i:s') }}</strong>
            </div>
            <div>
                <span class="text-gray-500">Período:</span>
                <strong class="text-gray-900 font-mono ml-1">{{ $periodoInicioFormatado }} a {{ $periodoFimFormatado }}</strong>
            </div>
            <div>
                <span class="text-gray-500">Emitido por:</span>
                <strong class="text-gray-900 ml-1">{{ auth()->user()?->nome ?? 'Administrador' }} ({{ auth()->user()?->codigo_operador ?? 'ADM' }})</strong>
            </div>
        </div>
    </div>

    <!-- RESUMO CONSOLIDADO SIMPLES: INDICADORES E FILTROS SEM POLUIÇÃO VISUAL -->
    <div class="mt-3 py-2.5 px-3.5 bg-gray-50 rounded-lg border border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs text-left">
        <!-- Indicadores essenciais -->
        <div class="flex items-center gap-3.5 flex-wrap">
            <div>
                <span class="text-gray-500 text-[11px]">Total:</span>
                <strong class="text-gray-900 font-mono text-xs sm:text-sm ml-1">{{ $reportStats['total'] }}</strong>
            </div>
            <div class="h-3.5 w-px bg-gray-300 hidden sm:block"></div>
            <div>
                <span class="text-gray-500 text-[11px]">Em Pátio:</span>
                <strong class="text-emerald-800 font-mono text-xs sm:text-sm ml-1">{{ $reportStats['entradas'] }}</strong>
            </div>
            <div class="h-3.5 w-px bg-gray-300 hidden sm:block"></div>
            <div>
                <span class="text-gray-500 text-[11px]">Saídas:</span>
                <strong class="text-amber-800 font-mono text-xs sm:text-sm ml-1">{{ $reportStats['saidas'] }}</strong>
            </div>
            <div class="h-3.5 w-px bg-gray-300 hidden sm:block"></div>
            <div>
                <span class="text-gray-500 text-[11px]">Docentes:</span>
                <strong class="text-gray-900 font-mono text-xs sm:text-sm ml-1">{{ $reportStats['docentes'] }}</strong>
            </div>
        </div>

        <!-- Filtros aplicados resumidos -->
        <div class="text-[11px] text-gray-600 sm:text-right">
            <span>Categoria: <strong>{{ $categoriasNomes[$categoryFilter] ?? 'Todas' }}</strong></span>
            @if ($dayOfWeekFilter !== 'todos')
                <span class="ml-1.5">• Dia: <strong>{{ $diasNomes[$dayOfWeekFilter] ?? ucfirst($dayOfWeekFilter) }}</strong></span>
            @endif
            @if (!empty(trim($driverSearch)))
                <span class="ml-1.5">• Busca: <strong>"{{ $driverSearch }}"</strong></span>
            @endif
            @if ($operatorFilter !== 'todos')
                <span class="ml-1.5">• Operador: <strong>{{ $operatorFilter }}</strong></span>
            @endif
        </div>
    </div>

    <!-- TABELA OFICIAL DE REGISTROS - SEM BARRA DE ROLAGEM HORIZONTAL (AJUSTE 100% LARGURA) -->
    <div class="mt-4 w-full">
        <table class="w-full text-left border-collapse border border-gray-300 text-[11px] table-fixed">
            <thead>
                <tr class="bg-gray-100 text-gray-800 uppercase text-[10px] font-bold tracking-wider border-b border-gray-300">
                    <th class="py-2 px-2 border-r border-gray-300 w-[12%] text-left">Protocolo</th>
                    <th class="py-2 px-2 border-r border-gray-300 w-[14%] text-left">Data & Dia</th>
                    <th class="py-2 px-2 border-r border-gray-300 w-[14%] text-left">Horários</th>
                    <th class="py-2 px-2 border-r border-gray-300 w-[12%] text-left">Placa</th>
                    <th class="py-2 px-2 border-r border-gray-300 w-[24%] text-left">Condutor & Vínculo</th>
                    <th class="py-2 px-2 border-r border-gray-300 w-[12%] text-left">Status</th>
                    <th class="py-2 px-2 w-[12%] text-left">Guarita</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($filteredData as $item)
                    <tr class="break-inside-avoid">
                        <!-- Protocolo -->
                        <td class="py-2 px-2 font-mono font-bold text-gray-900 border-r border-gray-200 truncate">
                            {{ $item['id'] }}
                        </td>

                        <!-- Data & Dia da Semana -->
                        <td class="py-2 px-2 border-r border-gray-200 leading-tight">
                            <div class="font-bold text-gray-900 font-mono">{{ $item['date_only'] }}</div>
                            <div class="text-[10px] text-gray-600 truncate">{{ $item['day_of_week_label'] }}</div>
                        </td>

                        <!-- Horários de Entrada e Saída -->
                        <td class="py-2 px-2 border-r border-gray-200 font-mono text-[10px] leading-tight">
                            <div class="text-gray-900 truncate">
                                <span class="text-gray-500">E:</span> <strong>{{ $item['entry_hour'] }}</strong>
                            </div>
                            <div class="text-gray-700 truncate">
                                <span class="text-gray-500">S:</span>
                                <strong>{{ $item['exit_hour'] ?? 'Em Pátio' }}</strong>
                            </div>
                        </td>

                        <!-- Placa -->
                        <td class="py-2 px-2 border-r border-gray-200 font-mono font-extrabold text-gray-900">
                            <span class="px-1.5 py-0.5 rounded bg-gray-100 border border-gray-300 text-[10px] inline-block">
                                {{ $item['plate'] }}
                            </span>
                        </td>

                        <!-- Condutor & Vínculo -->
                        <td class="py-2 px-2 border-r border-gray-200 leading-tight">
                            <div class="font-bold text-gray-900 truncate">
                                {{ $item['driver'] }}
                                @if (($item['occupant_type'] ?? 'condutor') === 'passageiro')
                                    <span class="text-[9px] uppercase px-1 py-0.2 rounded bg-gray-200 text-gray-700 font-bold ml-1">Carona</span>
                                @endif
                            </div>
                            <div class="text-[10px] text-gray-600 truncate">{{ $item['category_label'] }}</div>
                        </td>

                        <!-- Status & Permanência -->
                        <td class="py-2 px-2 border-r border-gray-200 leading-tight">
                            <div class="font-bold {{ $item['event'] === 'entrada' ? 'text-emerald-800' : 'text-amber-900' }} truncate">
                                {{ $item['event_label'] }}
                            </div>
                            <div class="text-[10px] text-gray-600 truncate">
                                {{ $item['permanencia'] }}
                            </div>
                        </td>

                        <!-- Operador & Guarita -->
                        <td class="py-2 px-2 leading-tight">
                            <div class="font-semibold text-gray-900 truncate">{{ $item['operator'] }}</div>
                            <div class="text-[10px] text-gray-600 truncate">{{ $item['gate'] }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-gray-500 font-medium">
                            Nenhum registro de acesso localizado para os parâmetros informados neste relatório.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- RODAPÉ DE VALIDAÇÃO INSTITUCIONAL SIMPLES -->
    <div class="mt-8 pt-5 border-t border-gray-300 flex flex-col gap-5 break-inside-avoid">
        <div class="grid grid-cols-1 sm:grid-cols-2 print:grid-cols-2 gap-8 text-center text-xs pt-2">
            <div class="flex flex-col items-center">
                <div class="w-52 sm:w-60 border-b border-gray-800 mb-1.5"></div>
                <span class="font-bold text-gray-900">{{ auth()->user()?->nome ?? 'Responsável pela Emissão' }}</span>
                <span class="text-[10px] text-gray-600 font-mono">
                    Código Operacional: {{ auth()->user()?->codigo_operador ?? 'ADM-001' }}
                </span>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-52 sm:w-60 border-b border-gray-800 mb-1.5"></div>
                <span class="font-bold text-gray-900">Diretoria de Serviços / Administração Predial</span>
                <span class="text-[10px] text-gray-600">FATEC • Centro Paula Souza</span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-[10px] text-gray-500 border-t border-gray-200 pt-2.5">
            <span>Documento gerado eletronicamente pelo Sistema Sentinela FATEC.</span>
            <span class="font-mono">Total listado no documento: {{ count($filteredData) }} registro(s)</span>
        </div>
    </div>
</div>
