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

    <!-- CONTEXT & TELEMETRIA OPERACIONAL (RF07 / RF08 / RF09) -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-xs">
        <div class="flex flex-col gap-1.5">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded bg-primary-container text-white text-[11px] font-bold uppercase tracking-wider">
                    RF07 • RF08 • RF09
                </span>
                <span class="text-on-surface-variant text-xs font-semibold uppercase tracking-wider">
                    Terminal Operacional de Guarita
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">
                Controle de Passagem de Veículos - Portaria Principal
            </h1>
            <p class="text-sm text-on-surface-variant">
                Monitoramento de telemetria OCR mobile em tempo real com liberação de cancela e contingência manual.
            </p>
        </div>

        <!-- Telemetria WebSocket Reverb + Simulação -->
        <div class="flex items-center gap-3 self-start lg:self-auto flex-wrap">
            <div class="flex items-center gap-2.5 px-3.5 py-2 bg-surface-container-low rounded-xl border border-surface-container/80">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-on-surface leading-tight">Reverb WebSocket</span>
                    <span class="text-[10px] text-on-surface-variant font-mono">wss://reverb.fatec.sp • 14ms</span>
                </div>
            </div>

            <!-- Botão de Simulação em Tempo Real -->
            <button 
                type="button" 
                wire:click="simulateNewCapture"
                class="px-3.5 py-2 bg-primary hover:bg-primary-container text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-xs transition-colors cursor-pointer"
                title="Simular passagem capturada pelo app móvel"
            >
                <span class="material-symbols-outlined text-[16px]">sensors</span>
                <span>Simular Captura OCR</span>
            </button>
        </div>
    </div>

    <!-- METRICS STRIP -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-on-surface-variant uppercase">Passagens Hoje</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-on-surface font-mono">{{ $totalPassages }}</div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-surface-container flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[24px]">directions_car</span>
            </div>
        </div>

        <div class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-on-surface-variant uppercase">Liberações Automáticas</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 font-mono">{{ $automaticPassages }}</div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <span class="material-symbols-outlined text-[24px]">task_alt</span>
            </div>
        </div>

        <div class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-on-surface-variant uppercase">Correções Manuais</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-amber-600 font-mono">{{ $manualCorrections }}</div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                <span class="material-symbols-outlined text-[24px]">edit_note</span>
            </div>
        </div>

        <div class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-on-surface-variant uppercase">Vagas Estacionamento</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-primary font-mono">
                    {{ $occupiedSpots }}<span class="text-sm text-on-surface-variant font-normal">/{{ $totalSpots }}</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-surface-container flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[24px]">local_parking</span>
            </div>
        </div>
    </div>

    <!-- FILTROS DE TRÁFEGO (RF10) -->
    <div class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container shadow-xs flex flex-col xl:flex-row gap-4 items-stretch xl:items-center justify-between">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 flex-1">
            <!-- Filtro de Data -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Filtro de Data</label>
                <div class="flex items-center gap-2 px-3 py-2 bg-surface-container-low rounded-xl border border-surface-container text-on-surface">
                    <span class="material-symbols-outlined text-[18px] text-primary">calendar_today</span>
                    <select wire:model.live="filterDate" class="bg-transparent w-full text-xs font-semibold outline-none cursor-pointer">
                        <option value="hoje">Hoje, {{ date('d/m/Y') }}</option>
                        <option value="ontem">Ontem</option>
                        <option value="7dias">Últimos 7 dias</option>
                        <option value="mes">Mês Atual</option>
                    </select>
                </div>
            </div>

            <!-- Busca por Placa -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Buscar por Placa</label>
                <div class="flex items-center gap-2 px-3 py-2 bg-surface-container-low rounded-xl border border-surface-container text-on-surface">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant">search</span>
                    <input 
                        wire:model.live.debounce.300ms="filterPlate" 
                        type="text" 
                        placeholder="Ex: BRA, ABC..."
                        class="bg-transparent w-full text-xs font-semibold outline-none uppercase placeholder:normal-case placeholder:text-on-surface-variant/70"
                    />
                </div>
            </div>

            <!-- Categoria -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Categoria</label>
                <div class="flex items-center gap-2 px-3 py-2 bg-surface-container-low rounded-xl border border-surface-container text-on-surface">
                    <span class="material-symbols-outlined text-[18px] text-primary">badge</span>
                    <select wire:model.live="filterCategory" class="bg-transparent w-full text-xs font-semibold outline-none cursor-pointer">
                        <option value="todos">Todas as Categorias</option>
                        <option value="professor">Professor (Docente)</option>
                        <option value="prestador">Prestador de Serviço</option>
                        <option value="visitante">Visitante / Externo</option>
                    </select>
                </div>
            </div>

            <!-- Status -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Status</label>
                <div class="flex items-center gap-2 px-3 py-2 bg-surface-container-low rounded-xl border border-surface-container text-on-surface">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant">filter_alt</span>
                    <select wire:model.live="filterStatus" class="bg-transparent w-full text-xs font-semibold outline-none cursor-pointer">
                        <option value="todos">Todos os Status</option>
                        <option value="pendente">Apenas Pendentes</option>
                        <option value="autorizado">Apenas Autorizados</option>
                        <option value="nao_cadastrado">Não Cadastrados</option>
                        <option value="saida">Saída Registrada</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Botão Limpar Filtros -->
        <div class="flex items-end">
            <button 
                type="button" 
                wire:click="$set('filterPlate', ''); $set('filterCategory', 'todos'); $set('filterStatus', 'todos');"
                class="h-10 px-4 bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold rounded-xl flex items-center justify-center gap-1.5 transition-colors cursor-pointer w-full xl:w-auto"
            >
                <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                <span>Limpar</span>
            </button>
        </div>
    </div>

    <!-- LISTA DE ENTRADA DE VEÍCULOS (AO VIVO) -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs overflow-hidden flex flex-col">
        <!-- Barra de Cabeçalho da Tabela -->
        <div class="p-4 bg-surface-container-low border-b border-surface-container flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-primary text-[22px]">nest_cam_floodlight</span>
                <h2 class="text-base font-bold text-on-surface">Transmissão Ativa da Cancela 01 (Entrada Principal)</h2>
                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase">Ao Vivo</span>
            </div>
            <span class="text-xs text-on-surface-variant">Atualizado em tempo real via WebSocket (Reverb)</span>
        </div>

        <!-- Tabela Responsiva -->
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container/60 text-on-surface-variant uppercase text-[11px] font-bold tracking-wider h-11 border-b border-surface-container">
                        <th class="py-2.5 px-4">Captura Mobile</th>
                        <th class="py-2.5 px-4">Placa Identificada</th>
                        <th class="py-2.5 px-4">Condutor & Categoria</th>
                        <th class="py-2.5 px-4">Horário</th>
                        <th class="py-2.5 px-4">Status</th>
                        <th class="py-2.5 px-4 text-right">Ações Operacionais</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container">
                    @forelse ($filteredRecords as $record)
                        <tr class="hover:bg-surface-container-low/60 transition-colors {{ $record['status'] === 'pendente' ? 'bg-amber-50/40' : '' }}">
                            <!-- Captura Mobile -->
                            <td class="py-3 px-4">
                                <div class="relative group w-24 h-14 rounded-lg overflow-hidden bg-surface-container-high border border-surface-container shadow-xs cursor-pointer">
                                    <img 
                                        src="{{ $record['image_url'] }}" 
                                        alt="Captura {{ $record['plate'] }}" 
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                    />
                                    <span class="absolute bottom-1 right-1 px-1 rounded bg-black/70 text-white text-[9px] font-mono leading-tight">
                                        #{{ $record['id'] }}
                                    </span>
                                </div>
                            </td>

                            <!-- Card de Placa Mercosul -->
                            <td class="py-3 px-4">
                                <div class="inline-flex flex-col rounded-md bg-white border border-gray-300 shadow-xs overflow-hidden w-28 text-center">
                                    <div class="bg-blue-800 text-white text-[9px] font-bold py-0.5 tracking-wider uppercase flex items-center justify-between px-1.5">
                                        <span>Brasil</span>
                                        <span class="text-[7px]">BR</span>
                                    </div>
                                    <div class="text-sm font-extrabold tracking-widest text-gray-900 py-1 font-mono">
                                        {{ $record['plate'] }}
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 mt-1 text-[11px] font-bold {{ $record['confidence'] >= 90 ? 'text-emerald-700' : ($record['confidence'] >= 75 ? 'text-amber-700' : 'text-primary') }}">
                                    <span class="material-symbols-outlined text-[14px]">
                                        {{ $record['confidence'] >= 90 ? 'check_circle' : 'warning' }}
                                    </span>
                                    <span>OCR: {{ $record['confidence'] }}%</span>
                                </div>
                            </td>

                            <!-- Condutor & Categoria -->
                            <td class="py-3 px-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-on-surface">
                                        {{ $record['driver_name'] }}
                                    </span>
                                    <span class="text-xs text-on-surface-variant">
                                        {{ $record['category_label'] }}
                                    </span>
                                </div>
                            </td>

                            <!-- Horário -->
                            <td class="py-3 px-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-on-surface font-mono">{{ $record['registered_at'] }}</span>
                                    <span class="text-[11px] text-on-surface-variant">{{ $record['time_ago'] }}</span>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                @if ($record['status'] === 'autorizado')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                        {{ $record['status_label'] }}
                                    </span>
                                @elseif ($record['status'] === 'pendente')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-bold">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        {{ $record['status_label'] }}
                                    </span>
                                @elseif ($record['status'] === 'nao_cadastrado')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-100 text-red-900 text-xs font-bold">
                                        <span class="w-2 h-2 rounded-full bg-red-600"></span>
                                        {{ $record['status_label'] }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container-high text-on-surface-variant text-xs font-bold">
                                        <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                                        {{ $record['status_label'] }}
                                    </span>
                                @endif
                            </td>

                            <!-- OS TRÊS BOTÕES REQUISITADOS PELO USUÁRIO -->
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5 flex-wrap justify-end">
                                    <!-- Botão 1: Corrigir Placa (Abre o Modal) -->
                                    <button 
                                        type="button" 
                                        wire:click="openCorrectionModal({{ $record['id'] }})"
                                        class="h-8 px-2.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold flex items-center gap-1 transition-colors shadow-xs cursor-pointer"
                                        title="Corrigir leitura de placa do OCR"
                                    >
                                        <span class="material-symbols-outlined text-[15px]">edit</span>
                                        <span>Corrigir Placa</span>
                                    </button>

                                    <!-- Botão 3: Se não for cadastrado, mas o professor tem acesso, permitir a entrada -->
                                    <button 
                                        type="button" 
                                        wire:click="allowManualEntry({{ $record['id'] }})"
                                        class="h-8 px-2.5 rounded-lg bg-primary hover:bg-primary-container text-white text-xs font-bold flex items-center gap-1 transition-colors shadow-xs cursor-pointer"
                                        title="Permitir entrada de docente por exceção"
                                    >
                                        <span class="material-symbols-outlined text-[15px]">badge</span>
                                        <span>Permitir Entrada (Prof)</span>
                                    </button>

                                    <!-- Botão 2: Marcar Saída -->
                                    <button 
                                        type="button" 
                                        wire:click="markExit({{ $record['id'] }})"
                                        class="h-8 px-2.5 rounded-lg bg-surface-container-high hover:bg-surface-container-highest text-on-surface text-xs font-bold flex items-center gap-1 transition-colors cursor-pointer"
                                        title="Registrar saída do veículo da instituição"
                                    >
                                        <span class="material-symbols-outlined text-[15px]">output</span>
                                        <span>Marcar Saída</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[40px] text-on-surface-variant/60 mb-2">search_off</span>
                                <p class="text-sm font-semibold">Nenhum veículo encontrado com os filtros selecionados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Rodapé da Tabela -->
        <div class="p-4 bg-surface-container-low border-t border-surface-container flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="text-xs text-on-surface-variant">
                Exibindo <strong class="text-on-surface font-bold">{{ count($filteredRecords) }}</strong> registros em monitoramento ativo.
            </span>
            <div class="flex items-center gap-1">
                <button type="button" class="px-3 py-1 rounded-lg bg-surface-container text-xs font-semibold text-on-surface cursor-pointer">Anterior</button>
                <button type="button" class="px-3 py-1 rounded-lg bg-primary text-xs font-bold text-white cursor-pointer">1</button>
                <button type="button" class="px-3 py-1 rounded-lg bg-surface-container text-xs font-semibold text-on-surface cursor-pointer">2</button>
                <button type="button" class="px-3 py-1 rounded-lg bg-surface-container text-xs font-semibold text-on-surface cursor-pointer">Próxima</button>
            </div>
        </div>
    </div>

    <!-- MODAL DE CORREÇÃO DE PLACA (RF08) -->
    @if ($showCorrectionModal && $selectedRecord)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-2xl bg-surface-container-lowest rounded-2xl shadow-2xl border border-surface-container overflow-hidden flex flex-col animate-in fade-in zoom-in duration-150">
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-primary text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center text-white">
                            <span class="material-symbols-outlined text-[22px]">document_scanner</span>
                        </div>
                        <div class="flex flex-col">
                            <h3 class="text-base font-bold text-white leading-tight">
                                Correção de Leitura de Placa - Registro #{{ $selectedRecord['id'] }}
                            </h3>
                            <span class="text-[11px] text-white/80">Intervenção Operacional Manual (RF08 / RF11)</span>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        wire:click="closeCorrectionModal"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-white/80 hover:text-white hover:bg-white/10 transition-colors cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 flex flex-col gap-5">
                    <!-- Foto Capturada com Região OCR -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-surface-container-low p-4 rounded-xl border border-surface-container">
                        <div class="flex flex-col gap-1.5">
                            <span class="text-[11px] font-bold text-on-surface-variant uppercase">Captura da Câmera (App Mobile)</span>
                            <div class="relative h-32 rounded-lg overflow-hidden bg-black flex items-center justify-center">
                                <img src="{{ $selectedRecord['image_url'] }}" alt="Captura" class="w-full h-full object-cover">
                                <div class="absolute inset-x-6 inset-y-6 rounded border-2 border-dashed border-amber-400 pointer-events-none flex items-end justify-start p-1">
                                    <span class="bg-amber-500 text-black font-extrabold text-[9px] px-1 rounded">Região OCR</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col justify-between">
                            <div>
                                <span class="text-[11px] font-bold text-on-surface-variant uppercase">Dados da Passagem</span>
                                <div class="mt-2 space-y-1.5 text-xs">
                                    <div class="flex justify-between py-1 border-b border-surface-container">
                                        <span class="text-on-surface-variant">Horário:</span>
                                        <span class="font-bold text-on-surface font-mono">{{ $selectedRecord['registered_at'] }}</span>
                                    </div>
                                    <div class="flex justify-between py-1 border-b border-surface-container">
                                        <span class="text-on-surface-variant">Leitura OCR Bruta:</span>
                                        <span class="font-bold text-on-surface font-mono">{{ $selectedRecord['raw_ocr'] }}</span>
                                    </div>
                                    <div class="flex justify-between py-1">
                                        <span class="text-on-surface-variant">Confiança OCR:</span>
                                        <span class="font-bold text-amber-600">{{ $selectedRecord['confidence'] }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campos de Correção -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-on-surface">
                                Placa Corrigida (Padrão Mercosul / Antigo)
                            </label>
                            <input 
                                wire:model="correctedPlate" 
                                type="text" 
                                class="w-full h-11 px-3 bg-surface-container-lowest border-2 border-primary/50 focus:border-primary text-on-surface font-mono font-extrabold text-base uppercase rounded-xl focus:outline-none tracking-widest text-center"
                                placeholder="Ex: BRA-2E19"
                            />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-on-surface">
                                Vincular Categoria
                            </label>
                            <select 
                                wire:model="correctionCategory"
                                class="w-full h-11 px-3 bg-surface-container-lowest border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary"
                            >
                                <option value="professor">Professor (Docente FATEC)</option>
                                <option value="funcionario">Funcionário Administrativo</option>
                                <option value="prestador">Prestador de Serviço</option>
                                <option value="visitante">Visitante / Convidado</option>
                            </select>
                        </div>
                    </div>

                    <!-- Justificativa -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-on-surface">
                            Justificativa da Correção (Auditoria RF11)
                        </label>
                        <select 
                            wire:model="correctionJustification"
                            class="w-full h-10 px-3 bg-surface-container-lowest border border-surface-container-highest text-on-surface text-xs font-medium rounded-xl focus:outline-none focus:border-primary"
                        >
                            <option value="Reflexo solar sobre o caractere da placa">Reflexo solar sobre o caractere da placa</option>
                            <option value="Caractere 8 confundido com B ou 0 com O">Caractere 8 confundido com B ou 0 com O</option>
                            <option value="Placa física suja / desgastada">Placa física suja / desgastada</option>
                            <option value="Ângulo de captura inadequado">Ângulo de captura inadequado</option>
                        </select>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-surface-container-low border-t border-surface-container flex items-center justify-end gap-3">
                    <button 
                        type="button" 
                        wire:click="closeCorrectionModal"
                        class="px-4 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold transition-colors cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        wire:click="confirmCorrection"
                        class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[18px]">verified</span>
                        <span>Confirmar Correção e Liberar Cancela</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
