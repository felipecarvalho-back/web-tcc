<div class="flex flex-col gap-6">
    <!-- CABEÇALHO DE RELATÓRIOS -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-xs no-print">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded bg-primary-container text-white text-[11px] font-bold uppercase tracking-wider">
                    Auditoria & Tráfego
                </span>
                <span class="text-on-surface-variant text-xs font-semibold uppercase tracking-wider">
                    Exportação em PDF
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">
                Relatórios Gerenciais de Tráfego
            </h1>
            <p class="text-sm text-on-surface-variant">
                Geração de relatórios analíticos de entradas, saídas, correções manuais e eventos de cancela com exportação em PDF.
            </p>
        </div>

        <!-- BOTÃO DE DESTAQUE: GERAR PDF -->
        <div class="flex items-center gap-3">
            <button 
                type="button" 
                onclick="window.print()"
                class="px-5 py-3 bg-primary hover:bg-primary-container text-white text-xs font-bold rounded-xl flex items-center gap-2 shadow-md hover:shadow-lg transition-all active:scale-[0.99] cursor-pointer"
            >
                <span class="material-symbols-outlined text-[20px]">picture_as_pdf</span>
                <span>Gerar Relatório em PDF</span>
            </button>
        </div>
    </div>

    <!-- PAINEL DE FILTROS SIMPLES -->
    <div class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container shadow-xs flex flex-col gap-4 no-print">
        <div class="flex items-center gap-2 pb-3 border-b border-surface-container">
            <span class="material-symbols-outlined text-primary text-[20px]">filter_alt</span>
            <h2 class="text-xs font-bold text-on-surface uppercase tracking-wider">Filtros de Tráfego e Ocorrências</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
            <!-- Data Inicial -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase">Data Início</label>
                <input 
                    wire:model.live="startDate" 
                    type="date" 
                    class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary"
                />
            </div>

            <!-- Data Final -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase">Data Fim</label>
                <input 
                    wire:model.live="endDate" 
                    type="date" 
                    class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary"
                />
            </div>

            <!-- Categoria -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase">Categoria</label>
                <select 
                    wire:model.live="categoryFilter" 
                    class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer"
                >
                    <option value="todos">Todas as Categorias</option>
                    <option value="professor">Professores (Docentes)</option>
                    <option value="funcionario">Funcionários Administrativos</option>
                    <option value="prestador">Prestadores de Serviço</option>
                    <option value="visitante">Visitantes</option>
                </select>
            </div>

            <!-- Tipo de Movimento -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase">Tipo de Evento</label>
                <select 
                    wire:model.live="eventType" 
                    class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer"
                >
                    <option value="todos">Todos os Eventos</option>
                    <option value="entrada">Apenas Entradas</option>
                    <option value="saida">Apenas Saídas</option>
                </select>
            </div>

            <!-- Operador -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase">Operador Guarita</label>
                <select 
                    wire:model.live="operatorFilter" 
                    class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer"
                >
                    <option value="todos">Todos os Operadores</option>
                    <option value="Guarda Silva">Guarda Silva</option>
                    <option value="Guarda Ribeiro">Guarda Ribeiro</option>
                    <option value="Sistema OCR Auto">Sistema OCR Auto</option>
                </select>
            </div>
        </div>
    </div>

    <!-- SUMÁRIO EXECUTIVO DO RELATÓRIO -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs">
            <span class="text-[11px] font-bold text-on-surface-variant uppercase">Registros Filtrados</span>
            <div class="text-2xl font-extrabold text-on-surface font-mono">{{ count($filteredData) }}</div>
        </div>
        <div class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs">
            <span class="text-[11px] font-bold text-on-surface-variant uppercase">Total Entradas</span>
            <div class="text-2xl font-extrabold text-emerald-700 font-mono">
                {{ count(array_filter($filteredData, fn($i) => $i['event'] === 'entrada')) }}
            </div>
        </div>
        <div class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs">
            <span class="text-[11px] font-bold text-on-surface-variant uppercase">Total Saídas</span>
            <div class="text-2xl font-extrabold text-amber-700 font-mono">
                {{ count(array_filter($filteredData, fn($i) => $i['event'] === 'saida')) }}
            </div>
        </div>
        <div class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs">
            <span class="text-[11px] font-bold text-on-surface-variant uppercase">Taxa de OCR Efetiva</span>
            <div class="text-2xl font-extrabold text-primary font-mono">98.2%</div>
        </div>
    </div>

    <!-- CABEÇALHO EXCLUSIVO PARA IMPRESSÃO / PDF -->
    <div class="hidden print-only p-6 border-b-2 border-black mb-6">
        <div class="flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-700">Governo do Estado de São Paulo • Centro Paula Souza</span>
                <h1 class="text-2xl font-black tracking-tight text-black">Sentinela FATEC • Relatório de Tráfego Veicular</h1>
                <span class="text-sm font-semibold text-gray-800">Unidade FATEC - Sistema Integrado de Controle de Portaria</span>
            </div>
            <div class="text-right text-xs">
                <div>Emissão: <strong>{{ date('d/m/Y H:i:s') }}</strong></div>
                <div>Período: <strong>{{ $startDate }} até {{ $endDate }}</strong></div>
                <div>Operador Responsável: <strong>Guarda Silva</strong></div>
            </div>
        </div>
    </div>

    <!-- TABELA CONSOLIDADA DO RELATÓRIO -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs overflow-hidden flex flex-col">
        <div class="p-4 bg-surface-container-low border-b border-surface-container flex items-center justify-between no-print">
            <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider">
                Listagem Detalhada de Movimentações
            </h3>
            <span class="text-xs text-on-surface-variant">
                Pronto para exportação em PDF via navegador
            </span>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container/60 text-on-surface-variant uppercase text-[11px] font-bold tracking-wider h-11 border-b border-surface-container">
                        <th class="py-2.5 px-4">Protocolo / Data</th>
                        <th class="py-2.5 px-4">Placa Mercosul</th>
                        <th class="py-2.5 px-4">Condutor & Categoria</th>
                        <th class="py-2.5 px-4">Tipo Movimentação</th>
                        <th class="py-2.5 px-4">Cancela</th>
                        <th class="py-2.5 px-4">Operador</th>
                        <th class="py-2.5 px-4 text-right">Confiança OCR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container text-xs">
                    @forelse ($filteredData as $item)
                        <tr class="hover:bg-surface-container-low/60 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-on-surface font-mono">{{ $item['id'] }}</span>
                                    <span class="text-on-surface-variant text-[11px] font-mono">{{ $item['date_time'] }}</span>
                                </div>
                            </td>

                            <td class="py-3 px-4">
                                <div class="inline-flex flex-col rounded bg-white border border-gray-300 shadow-xs overflow-hidden w-22 text-center">
                                    <div class="bg-blue-800 text-white text-[7px] font-bold py-0.5 tracking-wider uppercase">
                                        Brasil
                                    </div>
                                    <div class="text-[11px] font-extrabold tracking-wider text-gray-900 py-0.5 font-mono">
                                        {{ $item['plate'] }}
                                    </div>
                                </div>
                            </td>

                            <td class="py-3 px-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-on-surface">{{ $item['driver'] }}</span>
                                    <span class="text-on-surface-variant text-[11px]">{{ $item['category_label'] }}</span>
                                </div>
                            </td>

                            <td class="py-3 px-4">
                                @if ($item['event'] === 'entrada')
                                    <span class="inline-flex items-center gap-1 text-emerald-800 font-semibold">
                                        <span class="material-symbols-outlined text-[16px] text-emerald-600">input</span>
                                        <span>{{ $item['event_label'] }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-amber-800 font-semibold">
                                        <span class="material-symbols-outlined text-[16px] text-amber-600">output</span>
                                        <span>{{ $item['event_label'] }}</span>
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-on-surface-variant">
                                {{ $item['gate'] }}
                            </td>

                            <td class="py-3 px-4 font-semibold text-on-surface">
                                {{ $item['operator'] }}
                            </td>

                            <td class="py-3 px-4 text-right font-mono font-bold text-on-surface">
                                {{ $item['ocr_score'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-on-surface-variant">
                                <p class="text-sm font-semibold">Nenhum registro encontrado para os filtros selecionados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-surface-container-low border-t border-surface-container flex items-center justify-between text-xs text-on-surface-variant no-print">
            <span>Relatório gerado em tempo real com base no histórico de passagens.</span>
            <span class="font-mono">Página 1 de 1</span>
        </div>
    </div>

    <!-- RODAPÉ EXCLUSIVO PARA IMPRESSÃO / PDF -->
    <div class="hidden print-only mt-12 pt-8 border-t border-gray-400">
        <div class="grid grid-cols-2 gap-12 text-center text-xs">
            <div class="flex flex-col items-center">
                <div class="w-64 border-b border-black mb-2"></div>
                <span>Guarda Responsável pelo Turno</span>
                <span class="text-gray-600 font-mono">Código: GDA-104</span>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-64 border-b border-black mb-2"></div>
                <span>Diretoria de Serviços / Administração Predial</span>
                <span class="text-gray-600">FATEC / Centro Paula Souza</span>
            </div>
        </div>
    </div>
</div>
