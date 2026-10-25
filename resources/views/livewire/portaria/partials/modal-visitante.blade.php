@if ($showFormModal)
    <x-modal
        title="{{ $isEditing ? 'Editar Registro de Visitante' : 'Registrar Entrada de Visitante' }}"
        subtitle="Digite o CPF para buscar cadastros e veículos anteriores automaticamente"
        icon="{{ $isEditing ? 'edit_square' : 'how_to_reg' }}"
        onClose="closeFormModal"
        maxWidth="2xl"
    >
        <form wire:submit="registerAndReleaseGate" id="form-visitante" class="flex flex-col gap-4">
            <!-- LINHA 1: CPF & PLACA DO CARRO -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- CPF -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface flex items-center justify-between" for="cpf">
                        <span>CPF do Visitante *</span>
                        <span class="text-on-surface-variant font-normal text-[11px]">Busca automática</span>
                    </label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">badge</span>
                        <input
                            wire:model.live.debounce.300ms="cpf"
                            id="cpf"
                            type="text"
                            x-mask="999.999.999-99"
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
                            maxlength="10"
                            placeholder="Ex: BRA-2E19"
                            class="w-full h-11 pl-10 pr-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-sm rounded-xl focus:border-primary focus:outline-none transition-colors font-mono uppercase tracking-wider"
                        />
                    </div>
                    @error('plate') <span class="text-error text-[11px] font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- CARD DE CONFERÊNCIA VISUAL DO GUARDA (QUANDO O CPF JÁ EXISTE) -->
            @if ($recurringVisitor)
                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-950 flex flex-col gap-2">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-700 text-[20px]">verified_user</span>
                            <span class="text-xs font-extrabold uppercase tracking-wide text-amber-900">
                                Visitante Recorrente — Conferir Veículo na Cancela
                            </span>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-amber-200/70 text-amber-900 text-[10px] font-bold">
                            Dados Pré-preenchidos
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs bg-white/80 p-2.5 rounded-lg border border-amber-200/60">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-amber-700 block">Última Placa</span>
                            <span class="font-mono font-bold text-on-surface">{{ $recurringVisitor['placa'] }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-amber-700 block">Modelo Cadastrado</span>
                            <span class="font-semibold text-on-surface">{{ $recurringVisitor['modelo'] }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-amber-700 block">Cor do Veículo</span>
                            <span class="font-semibold text-on-surface">{{ $recurringVisitor['cor'] }}</span>
                        </div>
                    </div>
                    <p class="text-[11px] text-amber-800">
                        Verifique se a placa, modelo e cor conferem com o veículo na guarita. Caso ele esteja em outro carro hoje, basta alterar os campos.
                    </p>
                </div>
            @endif

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

            <!-- LINHA 3: NOME COMPLETO, MODELO & COR DO VEÍCULO -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <!-- Nome Completo (5 colunas) -->
                <div class="sm:col-span-5 flex flex-col gap-1.5">
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

                <!-- Modelo do Veículo (4 colunas) -->
                <div class="sm:col-span-4 flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface" for="vehicleModel">
                        Modelo do Veículo
                    </label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">minor_crash</span>
                        <input
                            wire:model="vehicleModel"
                            id="vehicleModel"
                            type="text"
                            placeholder="Ex: Chevrolet Onix"
                            class="w-full h-11 pl-10 pr-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-sm rounded-xl focus:border-primary focus:outline-none transition-colors"
                        />
                    </div>
                </div>

                <!-- Cor do Veículo (3 colunas) -->
                <div class="sm:col-span-3 flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface" for="vehicleColor">
                        Cor
                    </label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">palette</span>
                        <input
                            wire:model="vehicleColor"
                            id="vehicleColor"
                            type="text"
                            placeholder="Ex: Branco"
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
        </form>

        <x-slot:footer>
            <button
                type="button"
                wire:click="closeFormModal"
                class="px-4 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold transition-colors cursor-pointer"
            >
                Cancelar
            </button>
            <button
                type="submit"
                form="form-visitante"
                wire:loading.attr="disabled"
                class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container disabled:opacity-50 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">{{ $isEditing ? 'save' : 'garage' }}</span>
                <span>{{ $isEditing ? 'Salvar Alterações' : 'Cadastrar e Liberar Cancela' }}</span>
            </button>
        </x-slot:footer>
    </x-modal>
@endif
