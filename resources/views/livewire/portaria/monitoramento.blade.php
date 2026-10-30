<div wire:poll.5s.visible class="flex flex-col gap-6">
    <!-- TOAST NOTIFICATION -->
    <x-toast :message="$toastMessage" :type="$toastType" />

    <!-- CONTEXT & TELEMETRIA OPERACIONAL DA GUARITA -->
    <div
        class="flex flex-col gap-4 bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-col gap-1.5">
                <div class="flex items-center gap-2">
                    <span
                        class="px-2 py-0.5 rounded bg-primary-container text-white text-[11px] font-bold uppercase tracking-wider">
                        Terminal da Guarita
                    </span>
                    <span class="text-on-surface-variant text-xs font-semibold uppercase tracking-wider">
                        Posto de Acesso Principal
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">
                    Controle de Passagem de Veículos
                </h1>
                <p class="text-sm text-on-surface-variant max-w-3xl">
                    Monitoramento em tempo real com leitura automática de placas OCR. Libere a cancela para veículos
                    autorizados ou registre o bloqueio de acessos não cadastrados.
                </p>
            </div>

            <!-- Status de Conexão da Cancela e Câmera -->
            <div class="flex items-center gap-3 shrink-0">
                <div
                    class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Cancela 01:</span>
                    <strong class="font-extrabold">OPERACIONAL</strong>
                </div>
            </div>
        </div>

    </div>

    <!-- 4 INDICADORES OPERACIONAIS EM TEMPO REAL -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Aguardando Liberação na Cancela -->
        <div
            class="p-4 bg-surface-container-lowest rounded-2xl border {{ $pendingPassages > 0 ? 'border-amber-300 ring-2 ring-amber-400/20' : 'border-surface-container' }} shadow-xs flex flex-col justify-between gap-3 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] sm:text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                    Aguardando Liberação
                </span>
                <div
                    class="w-9 h-9 rounded-xl {{ $pendingPassages > 0 ? 'bg-amber-100 text-amber-900 animate-pulse' : 'bg-surface-container text-on-surface-variant' }} flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">hourglass_top</span>
                </div>
            </div>
            <div>
                <div
                    class="text-2xl sm:text-3xl font-extrabold font-mono {{ $pendingPassages > 0 ? 'text-amber-700' : 'text-on-surface' }}">
                    {{ $pendingPassages }}
                </div>
                <span
                    class="text-[11px] {{ $pendingPassages > 0 ? 'text-amber-800 font-bold' : 'text-on-surface-variant' }} mt-0.5 block truncate">
                    {{ $pendingPassages > 0 ? 'Veículo(s) na cancela!' : 'Fila livre no momento' }}
                </span>
            </div>
        </div>

        <!-- 2. Liberações Autorizadas Hoje -->
        <div
            class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] sm:text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                    Liberados Hoje
                </span>
                <div
                    class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">task_alt</span>
                </div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-700 font-mono">
                    {{ $automaticPassages }}
                </div>
                <span class="text-[11px] text-on-surface-variant mt-0.5 block truncate">
                    Veículos autorizados no campus
                </span>
            </div>
        </div>

        <!-- 3. Acessos Bloqueados na Guarita -->
        <div
            class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] sm:text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                    Acessos Bloqueados
                </span>
                <div class="w-9 h-9 rounded-xl bg-red-500/10 text-red-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">block</span>
                </div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-extrabold text-red-700 font-mono">
                    {{ $blockedPassages }}
                </div>
                <span class="text-[11px] text-on-surface-variant mt-0.5 block truncate">
                    Sem cadastro ou sem código
                </span>
            </div>
        </div>

        <!-- 4. Total de Passagens Hoje -->
        <div
            class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col justify-between gap-3 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] sm:text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                    Total de Passagens
                </span>
                <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">directions_car</span>
                </div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-extrabold text-on-surface font-mono">
                    {{ $totalPassages }}
                </div>
                <span class="text-[11px] text-on-surface-variant mt-0.5 block truncate">
                    Fluxo total capturado hoje
                </span>
            </div>
        </div>
    </div>

    <!-- FILTROS DE TRÁFEGO -->
    <div
        class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container shadow-xs flex flex-col xl:flex-row gap-4 items-stretch xl:items-center justify-between">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 flex-1">
            <!-- Filtro de Data -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Filtro de
                    Data</label>
                <div
                    class="flex items-center gap-2 px-3 py-2 bg-surface-container-low rounded-xl border border-surface-container text-on-surface">
                    <span class="material-symbols-outlined text-[18px] text-primary">calendar_today</span>
                    <select wire:model.live="filterDate"
                        class="bg-transparent w-full text-xs font-semibold outline-none cursor-pointer">
                        <option value="hoje">Hoje, {{ \Illuminate\Support\Carbon::now()->format('d/m/Y') }}</option>
                        <option value="ontem">Ontem</option>
                        <option value="7dias">Últimos 7 dias</option>
                        <option value="mes">Mês Atual</option>
                    </select>
                </div>
            </div>

            <!-- Busca por Placa -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Buscar por
                    Placa</label>
                <div
                    class="flex items-center gap-2 px-3 py-2 bg-surface-container-low rounded-xl border border-surface-container text-on-surface">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant">search</span>
                    <input wire:model.live.debounce.300ms="filterPlate" type="text" x-mask="***-****" maxlength="8"
                        placeholder="Ex: ABC-1234"
                        class="bg-transparent w-full text-xs font-semibold outline-none uppercase placeholder:normal-case placeholder:text-on-surface-variant/70 font-mono" />
                </div>
            </div>

            <!-- Categoria -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Categoria</label>
                <div
                    class="flex items-center gap-2 px-3 py-2 bg-surface-container-low rounded-xl border border-surface-container text-on-surface">
                    <span class="material-symbols-outlined text-[18px] text-primary">badge</span>
                    <select wire:model.live="filterCategory"
                        class="bg-transparent w-full text-xs font-semibold outline-none cursor-pointer">
                        <option value="todos">Todas as Categorias</option>
                        <option value="professor">Professor (Docente)</option>
                        <option value="funcionario">Servidor / Funcionário</option>
                        <option value="prestador">Prestador de Serviço</option>
                        <option value="visitante">Visitante / Externo</option>
                    </select>
                </div>
            </div>

            <!-- Status -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Status</label>
                <div
                    class="flex items-center gap-2 px-3 py-2 bg-surface-container-low rounded-xl border border-surface-container text-on-surface">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant">filter_alt</span>
                    <select wire:model.live="filterStatus"
                        class="bg-transparent w-full text-xs font-semibold outline-none cursor-pointer">
                        <option value="todos">Todos os Status</option>
                        <option value="pendente">Apenas Pendentes (Aguardando)</option>
                        <option value="autorizado">Apenas Autorizados (Em Pátio)</option>
                        <option value="nao_cadastrado">Não Cadastrados</option>
                        <option value="bloqueado">Acessos Bloqueados</option>
                        <option value="saida">Saída Registrada</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Botão Limpar Filtros -->
        <div class="flex items-end">
            <button type="button"
                wire:click="$set('filterPlate', ''); $set('filterCategory', 'todos'); $set('filterStatus', 'todos');"
                class="h-10 px-4 bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold rounded-xl flex items-center justify-center gap-1.5 transition-colors cursor-pointer w-full xl:w-auto">
                <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                <span>Limpar</span>
            </button>
        </div>
    </div>

    <!-- LISTA DE ENTRADA DE VEÍCULOS (ATUALIZAÇÃO AUTOMÁTICA) -->
    <div
        class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs overflow-hidden flex flex-col">
        <!-- Barra de Cabeçalho da Tabela -->
        <div
            class="p-4 bg-surface-container-low border-b border-surface-container flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-primary text-[22px]">nest_cam_floodlight</span>
                <h2 class="text-base font-bold text-on-surface">Fila da Cancela 01 (Entrada Principal)</h2>
                <span
                    class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase">Ao
                    Vivo</span>
            </div>
        </div>

        <!-- Tabela Responsiva -->
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-surface-container/60 text-on-surface-variant uppercase text-[11px] font-bold tracking-wider h-11 border-b border-surface-container">
                        <th class="py-2.5 px-4">Captura da Câmera</th>
                        <th class="py-2.5 px-4">Placa Identificada</th>
                        <th class="py-2.5 px-4">Condutor & Vínculo</th>
                        <th class="py-2.5 px-4">Horário</th>
                        <th class="py-2.5 px-4">Status</th>
                        <th class="py-2.5 px-4 text-right">Ações Operacionais da Cancela</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container">
                    @forelse ($filteredRecords as $record)
                        <tr wire:key="record-{{ $record['id'] }}"
                            class="hover:bg-surface-container-low/60 transition-colors {{ $record['status'] === 'pendente' ? 'bg-amber-50/40' : ($record['status'] === 'bloqueado' ? 'bg-red-50/20' : '') }}">
                            <!-- Captura Mobile -->
                            <td class="py-3 px-4">
                                <div
                                    class="relative group w-24 h-14 rounded-lg overflow-hidden bg-surface-container-high border border-surface-container shadow-xs cursor-pointer">
                                    <img src="{{ $record['image_url'] }}" alt="Captura {{ $record['plate'] }}"
                                        loading="lazy" decoding="async"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                                    <span
                                        class="absolute bottom-1 right-1 px-1 rounded bg-black/70 text-white text-[9px] font-mono leading-tight">
                                        #{{ $record['id'] }}
                                    </span>
                                </div>
                            </td>

                            <!-- Card de Placa Mercosul -->
                            <td class="py-3 px-4">
                                <x-mercosul-plate :plate="$record['plate']" />
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
                                    <span
                                        class="text-xs font-bold text-on-surface font-mono">{{ $record['registered_at'] }}</span>
                                    <span class="text-[11px] text-on-surface-variant">{{ $record['time_ago'] }}</span>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                @if ($record['status'] === 'autorizado')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                        {{ $record['status_label'] }}
                                    </span>
                                @elseif ($record['status'] === 'pendente')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-bold">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        {{ $record['status_label'] }}
                                    </span>
                                @elseif ($record['status'] === 'bloqueado')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-100 text-red-900 text-xs font-bold">
                                        <span class="material-symbols-outlined text-[13px] text-red-700">block</span>
                                        <span>{{ $record['status_label'] }}</span>
                                    </span>
                                @elseif ($record['status'] === 'nao_cadastrado')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-100 text-red-900 text-xs font-bold">
                                        <span class="w-2 h-2 rounded-full bg-red-600"></span>
                                        {{ $record['status_label'] }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container-high text-on-surface-variant text-xs font-bold">
                                        <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                                        {{ $record['status_label'] }}
                                    </span>
                                @endif
                            </td>

                            <!-- AÇÕES OPERACIONAIS CONFORME STATUS DA LEITURA -->
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5 flex-wrap justify-end">
                                    @if ($record['status'] === 'pendente')
                                        <!-- Caso 1: Placa reconhecida no banco -> Validar apenas o código de acesso para liberar -->
                                        <button type="button" wire:click="openDriverConfirmationModal({{ $record['id'] }})"
                                            class="h-8 px-2.5 rounded-lg bg-primary hover:bg-primary-container text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer"
                                            title="Validar código de acesso do condutor e liberar a cancela">
                                            <span class="material-symbols-outlined text-[16px]">how_to_reg</span>
                                            <span>Confirmar Condutor</span>
                                        </button>
                                    @elseif ($record['status'] === 'autorizado')
                                        <!-- Veículo já validado e liberado no pátio: Marcar Saída -->
                                        <button type="button" wire:click="markExit({{ $record['id'] }})"
                                            class="h-8 px-2.5 rounded-lg bg-surface-container-high hover:bg-surface-container-highest text-on-surface text-xs font-bold flex items-center gap-1 transition-colors cursor-pointer"
                                            title="Registrar saída do veículo">
                                            <span class="material-symbols-outlined text-[15px]">output</span>
                                            <span>Marcar Saída</span>
                                        </button>
                                    @elseif ($record['status'] === 'saida')
                                        <span class="text-[11px] font-semibold text-on-surface-variant px-2 py-1">
                                            Saída Concluída
                                        </span>
                                    @elseif ($record['status'] === 'bloqueado')
                                        <!-- Acesso Bloqueado: Permite visualizar ou reverter -->
                                        <span
                                            class="text-[11px] font-bold text-red-700 bg-red-50 border border-red-200 px-2 py-1 rounded-lg inline-flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">block</span>
                                            <span>Bloqueado</span>
                                        </span>
                                        <button type="button" wire:click="unblockRecord({{ $record['id'] }})"
                                            class="h-8 px-2.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-semibold flex items-center gap-1 transition-colors cursor-pointer"
                                            title="Reverter bloqueio para permitir nova avaliação na guarita">
                                            <span class="material-symbols-outlined text-[14px]">undo</span>
                                            <span>Reabrir</span>
                                        </button>
                                    @else
                                        <!-- Casos 2 e 3: Placa NÃO cadastrada / falha no OCR OU Bloqueio por falta de código -->
                                        <button type="button" wire:click="openVerificationModal({{ $record['id'] }})"
                                            class="h-8 px-2.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer"
                                            title="Corrigir placa + código de acesso ou liberar carro de terceiro">
                                            <span class="material-symbols-outlined text-[16px]">fact_check</span>
                                            <span>Verificar e Liberar</span>
                                        </button>

                                        <!-- BOTÃO BLOQUEAR ACESSO (QUANDO NÃO TIVER CÓDIGO OU NÃO ESTIVER CADASTRADO) -->
                                        <button type="button" wire:click="openBlockModal({{ $record['id'] }})"
                                            class="h-8 px-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-bold flex items-center gap-1 transition-colors shadow-xs cursor-pointer"
                                            title="Veículo sem cadastro e sem código: bloquear acesso na guarita">
                                            <span class="material-symbols-outlined text-[15px]">block</span>
                                            <span>Bloquear</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-on-surface-variant">
                                <span
                                    class="material-symbols-outlined text-[40px] text-on-surface-variant/60 mb-2">search_off</span>
                                <p class="text-sm font-semibold">Nenhum veículo encontrado com os filtros selecionados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Rodapé da Tabela -->
        <div
            class="p-4 bg-surface-container-low border-t border-surface-container flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="text-xs text-on-surface-variant">
                Exibindo <strong class="text-on-surface font-bold">{{ $filteredRecords->count() }}</strong> de <strong
                    class="text-on-surface font-bold">{{ $filteredRecords->total() }}</strong> registros em
                monitoramento ativo.
            </span>
            @if ($filteredRecords->hasPages())
                <div>
                    {{ $filteredRecords->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL DE DUPLA VERIFICAÇÃO (PLACA NÃO IDENTIFICADA OU NÃO CADASTRADA) -->
    @include('livewire.portaria.partials.modal-verificacao-acesso')

    <!-- MODAL DE CONFIRMAÇÃO DE CONDUTOR (VEÍCULO IDENTIFICADO) -->
    @include('livewire.portaria.partials.modal-confirmacao-condutor')

    <!-- MODAL DE BLOQUEIO DE ACESSO (SEM CÓDIGO / NÃO AUTORIZADO) -->
    @include('livewire.portaria.partials.modal-bloqueio-acesso')
</div>