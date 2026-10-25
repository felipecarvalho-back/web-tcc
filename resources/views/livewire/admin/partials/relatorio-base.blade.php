@php
    $diasNomes = [
        'todos' => 'Todos os dias da semana',
        'segunda' => 'Segundas-feiras',
        'terca' => 'Terças-feiras',
        'quarta' => 'Quartas-feiras',
        'quinta' => 'Quintas-feiras',
        'sexta' => 'Sextas-feiras',
        'sabado' => 'Sábados',
        'domingo' => 'Domingos',
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

<div class="bg-white text-gray-900 p-6 sm:p-8 rounded-xl border border-gray-300 shadow-xs print:border-0 print:shadow-none print:p-0">
    <!-- CABEÇALHO INSTITUCIONAL DO DOCUMENTO -->
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-5 border-b-2 border-gray-900">
        <div class="flex items-start gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-primary text-white flex items-center justify-center font-black text-lg shrink-0 print:border print:border-gray-900 print:text-gray-900 print:bg-gray-100">
                SF
            </div>
            <div class="flex flex-col">
                <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600">
                    Governo do Estado de São Paulo • Centro Paula Souza
                </span>
                <h2 class="text-lg sm:text-xl font-black tracking-tight text-gray-900 uppercase">
                    Relatório Oficial de Tráfego e Assiduidade
                </h2>
                <span class="text-xs font-semibold text-gray-700">
                    Sentinela FATEC • Sistema Integrado de Monitoramento e Portaria
                </span>
            </div>
        </div>

        <div class="flex flex-col sm:items-end text-xs text-gray-700 font-mono bg-gray-50 px-3.5 py-2.5 rounded-lg border border-gray-200">
            <div>
                <span class="text-gray-500">Emissão:</span>
                <strong class="text-gray-900">{{ \Illuminate\Support\Carbon::now()->format('d/m/Y H:i:s') }}</strong>
            </div>
            <div>
                <span class="text-gray-500">Período:</span>
                <strong class="text-gray-900">{{ $periodoInicioFormatado }} a {{ $periodoFimFormatado }}</strong>
            </div>
            <div>
                <span class="text-gray-500">Emitido por:</span>
                <strong class="text-gray-900">{{ auth()->user()?->nome ?? 'Administrador' }} ({{ auth()->user()?->codigo_operador ?? 'ADM' }})</strong>
            </div>
        </div>
    </div>

    <!-- PARÂMETROS E FILTROS APLICADOS NA AUDITORIA -->
    <div class="mt-4 p-3.5 bg-gray-50 rounded-lg border border-gray-200 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
        <div>
            <span class="text-[10px] font-bold uppercase text-gray-500 block">Filtro de Categoria</span>
            <strong class="text-gray-900 font-semibold">{{ $categoriasNomes[$categoryFilter] ?? 'Todas' }}</strong>
        </div>
        <div>
            <span class="text-[10px] font-bold uppercase text-gray-500 block">Dia da Semana (Frequência)</span>
            <strong class="text-gray-900 font-semibold">{{ $diasNomes[$dayOfWeekFilter] ?? 'Todos os dias' }}</strong>
        </div>
        <div>
            <span class="text-[10px] font-bold uppercase text-gray-500 block">Condutor / Placa Pesquisada</span>
            <strong class="text-gray-900 font-semibold">{{ !empty(trim($driverSearch)) ? $driverSearch : 'Sem restrição nominal' }}</strong>
        </div>
        <div>
            <span class="text-[10px] font-bold uppercase text-gray-500 block">Operador de Guarita</span>
            <strong class="text-gray-900 font-semibold">{{ $operatorFilter !== 'todos' ? $operatorFilter : 'Todos os operadores' }}</strong>
        </div>
    </div>

    <!-- RESUMO EXECUTIVO DE INDICADORES -->
    <div class="mt-4 grid grid-cols-4 gap-3">
        <div class="p-3 rounded-lg border border-gray-300 bg-white">
            <span class="text-[10px] font-bold uppercase text-gray-500 block">Total Filtrado</span>
            <span class="text-xl font-black text-gray-900 font-mono">{{ $reportStats['total'] }}</span>
            <span class="text-[10px] text-gray-500 block">passagens no período</span>
        </div>
        <div class="p-3 rounded-lg border border-gray-300 bg-white">
            <span class="text-[10px] font-bold uppercase text-gray-500 block">Em Pátio (Ativos)</span>
            <span class="text-xl font-black text-emerald-800 font-mono">{{ $reportStats['entradas'] }}</span>
            <span class="text-[10px] text-gray-500 block">sem baixa de saída</span>
        </div>
        <div class="p-3 rounded-lg border border-gray-300 bg-white">
            <span class="text-[10px] font-bold uppercase text-gray-500 block">Saídas Concluídas</span>
            <span class="text-xl font-black text-amber-800 font-mono">{{ $reportStats['saidas'] }}</span>
            <span class="text-[10px] text-gray-500 block">ciclo finalizado</span>
        </div>
        <div class="p-3 rounded-lg border border-gray-300 bg-white">
            <span class="text-[10px] font-bold uppercase text-gray-500 block">Acessos Docentes</span>
            <span class="text-xl font-black text-primary font-mono">{{ $reportStats['docentes'] }}</span>
            <span class="text-[10px] text-gray-500 block">professores identificados</span>
        </div>
    </div>

    <!-- TABELA OFICIAL DE REGISTROS DO RELATÓRIO -->
    <div class="mt-5 overflow-x-auto">
        <table class="w-full text-left border-collapse border border-gray-300 text-[11px]">
            <thead>
                <tr class="bg-gray-100 text-gray-800 uppercase text-[10px] font-bold tracking-wider border-b border-gray-300">
                    <th class="py-2 px-2.5 border-r border-gray-300">Protocolo</th>
                    <th class="py-2 px-2.5 border-r border-gray-300">Data & Dia</th>
                    <th class="py-2 px-2.5 border-r border-gray-300">Entrada / Saída</th>
                    <th class="py-2 px-2.5 border-r border-gray-300">Placa</th>
                    <th class="py-2 px-2.5 border-r border-gray-300">Condutor & Vínculo Institucional</th>
                    <th class="py-2 px-2.5 border-r border-gray-300">Movimentação & Permanência</th>
                    <th class="py-2 px-2.5">Cancela & Operador</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($filteredData as $item)
                    <tr class="break-inside-avoid">
                        <td class="py-2 px-2.5 font-mono font-bold text-gray-900 border-r border-gray-200 whitespace-nowrap">
                            {{ $item['id'] }}
                        </td>
                        <td class="py-2 px-2.5 border-r border-gray-200 whitespace-nowrap">
                            <div class="font-bold text-gray-900 font-mono">{{ $item['date_only'] }}</div>
                            <div class="text-[10px] text-gray-600">{{ $item['day_of_week_label'] }}</div>
                        </td>
                        <td class="py-2 px-2.5 border-r border-gray-200 font-mono whitespace-nowrap">
                            <div class="text-gray-900">
                                <span class="text-gray-500">Ent:</span> <strong>{{ $item['entry_hour'] }}</strong>
                            </div>
                            <div class="text-gray-700">
                                <span class="text-gray-500">Saí:</span>
                                <strong>{{ $item['exit_hour'] ?? 'Em Pátio' }}</strong>
                            </div>
                        </td>
                        <td class="py-2 px-2.5 border-r border-gray-200 font-mono font-extrabold text-gray-900 whitespace-nowrap">
                            <span class="px-1.5 py-0.5 rounded bg-gray-100 border border-gray-300">
                                {{ $item['plate'] }}
                            </span>
                        </td>
                        <td class="py-2 px-2.5 border-r border-gray-200">
                            <div class="font-bold text-gray-900">
                                {{ $item['driver'] }}
                                @if (($item['occupant_type'] ?? 'condutor') === 'passageiro')
                                    <span class="text-[9px] uppercase px-1 py-0.5 rounded bg-gray-200 text-gray-700 font-bold ml-1">Carona</span>
                                @endif
                            </div>
                            <div class="text-[10px] text-gray-600">{{ $item['category_label'] }}</div>
                        </td>
                        <td class="py-2 px-2.5 border-r border-gray-200">
                            <div class="font-bold {{ $item['event'] === 'entrada' ? 'text-emerald-800' : 'text-amber-900' }}">
                                {{ $item['event_label'] }}
                            </div>
                            <div class="text-[10px] text-gray-600">
                                Permanência: <strong>{{ $item['permanencia'] }}</strong>
                            </div>
                        </td>
                        <td class="py-2 px-2.5">
                            <div class="font-semibold text-gray-900">{{ $item['operator'] }}</div>
                            <div class="text-[10px] text-gray-600">{{ $item['gate'] }}</div>
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

    <!-- RODAPÉ DE ASSINATURAS E VALIDAÇÃO INSTITUCIONAL -->
    <div class="mt-10 pt-6 border-t border-gray-300 flex flex-col gap-6 break-inside-avoid">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-10 text-center text-xs pt-4">
            <div class="flex flex-col items-center">
                <div class="w-64 border-b border-gray-800 mb-2"></div>
                <span class="font-bold text-gray-900">{{ auth()->user()?->nome ?? 'Responsável pela Emissão' }}</span>
                <span class="text-[11px] text-gray-600 font-mono">
                    Código Operacional: {{ auth()->user()?->codigo_operador ?? 'ADM-001' }}
                </span>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-64 border-b border-gray-800 mb-2"></div>
                <span class="font-bold text-gray-900">Diretoria de Serviços / Administração Predial</span>
                <span class="text-[11px] text-gray-600">FATEC • Centro Paula Souza</span>
            </div>
        </div>

        <div class="flex items-center justify-between text-[10px] text-gray-500 border-t border-gray-200 pt-3">
            <span>Documento gerado eletronicamente pelo Sistema Sentinela FATEC.</span>
            <span class="font-mono">Total listado no documento: {{ count($filteredData) }} registro(s)</span>
        </div>
    </div>
</div>
