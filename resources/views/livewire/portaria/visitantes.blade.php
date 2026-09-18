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
                Cadastro Rápido de Visitantes
            </h1>
            <p class="text-sm text-on-surface-variant">
                Registro instantâneo de visitantes e veículos não credenciados para autorização e liberação imediata da cancela.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a 
                href="{{ route('portaria.monitoramento') }}"
                class="px-4 py-2 bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold rounded-xl flex items-center gap-1.5 transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Voltar ao Monitoramento</span>
            </a>
        </div>
    </div>

    <!-- GRID: FORMULÁRIO PRINCIPAL & VISITANTES RECENTES -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- FORMULÁRIO DE CADASTRO RÁPIDO (7 COLUNAS) -->
        <div class="lg:col-span-7 bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-xs flex flex-col gap-5">
            <div class="flex items-center justify-between pb-4 border-b border-surface-container">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]">how_to_reg</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-on-surface">Formulário de Entrada Imediata</h2>
                        <span class="text-xs text-on-surface-variant">Preencha os dados do condutor e do veículo</span>
                    </div>
                </div>
                <span class="text-[11px] font-bold text-primary bg-primary/10 px-2 py-1 rounded-md">
                    * Campos Obrigatórios
                </span>
            </div>

            <form wire:submit="registerAndReleaseGate" class="flex flex-col gap-4">
                <!-- LINHA 1: CPF & PLACA DO CARRO -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- CPF -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-on-surface flex items-center justify-between" for="cpf">
                            <span>CPF do Visitante *</span>
                            <span class="text-on-surface-variant font-normal text-[11px]">Apenas números</span>
                        </label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">badge</span>
                            <input 
                                wire:model="cpf" 
                                id="cpf" 
                                type="text" 
                                placeholder="000.000.000-00" 
                                class="w-full h-11 pl-10 pr-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-sm rounded-xl focus:border-primary focus:outline-none transition-colors font-mono"
                            />
                        </div>
                        @error('cpf') <span class="text-error text-[11px] font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <!-- Placa do Carro -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-on-surface" for="plate">
                            Placa do Carro *
                        </label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">directions_car</span>
                            <input 
                                wire:model="plate" 
                                id="plate" 
                                type="text" 
                                placeholder="Ex: BRA-2E19" 
                                class="w-full h-11 pl-10 pr-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-sm rounded-xl focus:border-primary focus:outline-none transition-colors font-mono uppercase tracking-wider"
                            />
                        </div>
                        @error('plate') <span class="text-error text-[11px] font-semibold">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- LINHA 2: MOTIVO DA VISITA -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface" for="visitReason">
                        Motivo da Visita *
                    </label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">chat</span>
                        <input 
                            wire:model="visitReason" 
                            id="visitReason" 
                            type="text" 
                            placeholder="Ex: Reunião com Coordenação DSM, Banca de TCC, Entrega técnica..." 
                            class="w-full h-11 pl-10 pr-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-sm rounded-xl focus:border-primary focus:outline-none transition-colors"
                        />
                    </div>
                    @error('visitReason') <span class="text-error text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>

                <!-- LINHA 3: NOME COMPLETO & MODELO DO VEÍCULO -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nome Completo -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-on-surface" for="visitorName">
                            Nome Completo do Visitante
                        </label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">person</span>
                            <input 
                                wire:model="visitorName" 
                                id="visitorName" 
                                type="text" 
                                placeholder="Ex: João Ferreira da Silva" 
                                class="w-full h-11 pl-10 pr-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-sm rounded-xl focus:border-primary focus:outline-none transition-colors"
                            />
                        </div>
                    </div>

                    <!-- Modelo / Cor do Veículo -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-on-surface" for="vehicleModel">
                            Modelo / Cor do Veículo
                        </label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">minor_crash</span>
                            <input 
                                wire:model="vehicleModel" 
                                id="vehicleModel" 
                                type="text" 
                                placeholder="Ex: Chevrolet Onix Branco" 
                                class="w-full h-11 pl-10 pr-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-sm rounded-xl focus:border-primary focus:outline-none transition-colors"
                            />
                        </div>
                    </div>
                </div>

                <!-- LINHA 4: DESTINO / SETOR & TEMPO DE PERMANÊNCIA -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-on-surface" for="department">
                            Setor / Bloco de Destino
                        </label>
                        <select 
                            wire:model="department" 
                            id="department" 
                            class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer"
                        >
                            <option value="DSM - Coordenação DSM">Bloco A • Coordenação DSM</option>
                            <option value="GTI - Coordenação GTI">Bloco A • Coordenação GTI</option>
                            <option value="Direção / Recursos Humanos">Bloco Administrativo • Direção & RH</option>
                            <option value="Secretaria Acadêmica">Bloco Administrativo • Secretaria</option>
                            <option value="Laboratórios Técnicos">Bloco B • Laboratórios de Informática</option>
                            <option value="Biblioteca / Auditório">Bloco C • Biblioteca & Auditório</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-on-surface" for="estimatedStay">
                            Permanência Estimada
                        </label>
                        <select 
                            wire:model="estimatedStay" 
                            id="estimatedStay" 
                            class="w-full h-11 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer"
                        >
                            <option value="30 minutos">Até 30 minutos (Rápida)</option>
                            <option value="1 hora">Até 1 hora</option>
                            <option value="2 horas">Até 2 horas</option>
                            <option value="Período Integral">Período Integral (Manhã / Noite)</option>
                        </select>
                    </div>
                </div>

                <!-- BOTÕES DE AÇÃO -->
                <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-surface-container">
                    <button 
                        type="submit" 
                        class="w-full sm:flex-1 h-12 bg-primary hover:bg-primary-container text-white font-bold text-sm rounded-xl flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-all active:scale-[0.99] cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[22px]">garage</span>
                        <span>Cadastrar e Liberar Cancela Imediatamente</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- LISTA LATERAL: VISITANTES REGISTRADOS HOJE (5 COLUNAS) -->
        <div class="lg:col-span-5 bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-xs flex flex-col gap-4">
            <div class="flex items-center justify-between pb-3 border-b border-surface-container">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[22px]">history</span>
                    <h3 class="text-base font-bold text-on-surface">Visitantes no Campus Hoje</h3>
                </div>
                <span class="text-xs font-bold text-on-surface-variant font-mono">
                    {{ count($recentVisitors) }} registros
                </span>
            </div>

            <div class="flex flex-col gap-3 overflow-y-auto max-h-[580px] pr-1">
                @forelse ($recentVisitors as $visitor)
                    <div class="p-4 rounded-xl border border-surface-container bg-surface-container-low/60 hover:bg-surface-container-low transition-colors flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <!-- Card de Placa -->
                                <div class="inline-flex flex-col rounded bg-white border border-gray-300 shadow-xs overflow-hidden w-20 text-center shrink-0">
                                    <div class="bg-blue-800 text-white text-[7px] font-bold py-0.5 tracking-wider uppercase">
                                        Brasil
                                    </div>
                                    <div class="text-[11px] font-extrabold tracking-wider text-gray-900 py-0.5 font-mono">
                                        {{ $visitor['plate'] }}
                                    </div>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-xs font-bold text-on-surface truncate">{{ $visitor['name'] }}</span>
                                    <span class="text-[11px] text-on-surface-variant font-mono">{{ $visitor['cpf'] }}</span>
                                </div>
                            </div>

                            @if ($visitor['status'] === 'ativo')
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold shrink-0">
                                    No Campus
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-[10px] font-bold shrink-0">
                                    Finalizado
                                </span>
                            @endif
                        </div>

                        <div class="text-xs text-on-surface-variant bg-surface-container-lowest p-2 rounded-lg border border-surface-container/60 space-y-1">
                            <div class="flex justify-between">
                                <span class="font-semibold text-on-surface">Motivo:</span>
                                <span class="text-right truncate max-w-[180px]">{{ $visitor['reason'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-semibold text-on-surface">Destino:</span>
                                <span>{{ $visitor['department'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-semibold text-on-surface">Entrada:</span>
                                <span class="font-mono">{{ $visitor['entry_time'] }} ({{ $visitor['stay'] }})</span>
                            </div>
                        </div>

                        @if ($visitor['status'] === 'ativo')
                            <button 
                                type="button" 
                                wire:click="markVisitorExit({{ $visitor['id'] }})"
                                class="w-full py-1.5 bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold rounded-lg flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
                            >
                                <span class="material-symbols-outlined text-[16px]">logout</span>
                                <span>Marcar Saída do Visitante</span>
                            </button>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-center text-on-surface-variant">
                        <p class="text-xs">Nenhum visitante registrado até o momento hoje.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
