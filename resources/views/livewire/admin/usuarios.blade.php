<div class="flex flex-col gap-6">
    <!-- CABEÇALHO -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-xs">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded bg-primary-container text-white text-[11px] font-bold uppercase tracking-wider">
                    Controle de Acessos
                </span>
                <span class="text-on-surface-variant text-xs font-semibold uppercase tracking-wider">
                    Operadores & Administradores
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">
                Gestão de Usuários do Sistema
            </h1>
            <p class="text-sm text-on-surface-variant">
                Gerenciamento de contas de acesso, porteiros da guarita, supervisores e administradores do Sentinela FATEC.
            </p>
        </div>

        <button 
            type="button" 
            wire:click="openCreateModal"
            class="px-5 py-3 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold flex items-center gap-2 shadow-md hover:shadow-lg transition-all active:scale-[0.99] cursor-pointer self-start sm:self-auto shrink-0"
        >
            <span class="material-symbols-outlined text-[20px]">person_add</span>
            <span>Novo Usuário</span>
        </button>
    </div>

    <!-- CARDS DE RESUMO DE USUÁRIOS -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col gap-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-on-surface-variant uppercase">Total Usuários</span>
                <span class="material-symbols-outlined text-primary text-[20px]">group</span>
            </div>
            <div class="text-2xl font-extrabold text-on-surface font-mono">{{ count($usuarios) }}</div>
            <span class="text-[11px] text-on-surface-variant">Cadastrados no banco</span>
        </div>

        <div class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col gap-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-on-surface-variant uppercase">Administradores</span>
                <span class="material-symbols-outlined text-purple-700 text-[20px]">admin_panel_settings</span>
            </div>
            <div class="text-2xl font-extrabold text-purple-900 font-mono">
                {{ count(array_filter($usuarios, fn($u) => $u['perfil'] === 'admin')) }}
            </div>
            <span class="text-[11px] text-on-surface-variant">Acesso total</span>
        </div>

        <div class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col gap-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-on-surface-variant uppercase">Porteiros / Operadores</span>
                <span class="material-symbols-outlined text-emerald-700 text-[20px]">badge</span>
            </div>
            <div class="text-2xl font-extrabold text-emerald-800 font-mono">
                {{ count(array_filter($usuarios, fn($u) => $u['perfil'] === 'operador')) }}
            </div>
            <span class="text-[11px] text-on-surface-variant">Operam as guaritas</span>
        </div>

        <div class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col gap-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-on-surface-variant uppercase">Supervisores</span>
                <span class="material-symbols-outlined text-blue-700 text-[20px]">security</span>
            </div>
            <div class="text-2xl font-extrabold text-blue-900 font-mono">
                {{ count(array_filter($usuarios, fn($u) => $u['perfil'] === 'supervisor')) }}
            </div>
            <span class="text-[11px] text-on-surface-variant">Gestão do turno</span>
        </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    @if ($toastMessage)
        <div class="p-4 rounded-xl flex items-center justify-between gap-3 shadow-md transition-all {{ $toastType === 'success' ? 'bg-emerald-900 text-white' : ($toastType === 'info' ? 'bg-blue-900 text-white' : 'bg-red-900 text-white') }}">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-[20px]">
                    {{ $toastType === 'success' ? 'check_circle' : ($toastType === 'info' ? 'info' : 'warning') }}
                </span>
                <span class="text-xs font-bold">{{ $toastMessage }}</span>
            </div>
            <button type="button" wire:click="clearToast" class="text-white/80 hover:text-white cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @endif

    <!-- PAINEL DE FILTROS & BUSCA -->
    <div class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container shadow-xs flex flex-col gap-4">
        <div class="flex items-center gap-2 pb-3 border-b border-surface-container">
            <span class="material-symbols-outlined text-primary text-[20px]">filter_alt</span>
            <h2 class="text-xs font-bold text-on-surface uppercase tracking-wider">Filtros de Pesquisa</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            <!-- Busca Geral -->
            <div class="flex flex-col gap-1 lg:col-span-2">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase">Buscar Usuário</label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[18px] pointer-events-none">search</span>
                    <input 
                        wire:model.live.debounce.300ms="search" 
                        type="text" 
                        placeholder="Nome, CPF, e-mail ou código de operador..."
                        class="w-full h-10 pl-10 pr-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary"
                    />
                </div>
            </div>

            <!-- Filtro por Perfil -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase">Perfil de Acesso</label>
                <select 
                    wire:model.live="perfilFilter" 
                    class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer"
                >
                    <option value="todos">Todos os Perfis</option>
                    <option value="admin">Administrador Geral</option>
                    <option value="operador">Operador de Portaria</option>
                    <option value="supervisor">Supervisor de Segurança</option>
                </select>
            </div>

            <!-- Filtro por Status -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase">Status da Conta</label>
                <select 
                    wire:model.live="statusFilter" 
                    class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer"
                >
                    <option value="todos">Todos os Status</option>
                    <option value="ativo">Apenas Ativos</option>
                    <option value="inativo">Apenas Inativos</option>
                </select>
            </div>
        </div>
    </div>

    <!-- TABELA DE USUÁRIOS -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs overflow-hidden flex flex-col">
        <div class="p-4 bg-surface-container-low border-b border-surface-container flex items-center justify-between">
            <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider">
                Usuários Cadastrados
            </h3>
            <span class="text-xs text-on-surface-variant">
                Exibindo <strong class="text-on-surface">{{ count($filteredUsuarios) }}</strong> registro(s)
            </span>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container/60 text-on-surface-variant uppercase text-[11px] font-bold tracking-wider h-11 border-b border-surface-container">
                        <th class="py-2.5 px-4">Usuário</th>
                        <th class="py-2.5 px-4">CPF</th>
                        <th class="py-2.5 px-4">Código de Acesso</th>
                        <th class="py-2.5 px-4">Perfil</th>
                        <th class="py-2.5 px-4">Status</th>
                        <th class="py-2.5 px-4">Data Cadastro</th>
                        <th class="py-2.5 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container text-xs">
                    @forelse ($filteredUsuarios as $user)
                        <tr class="hover:bg-surface-container-low/60 transition-colors">
                            <!-- Nome & E-mail -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary/10 text-primary font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ substr($user['nome'], 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-on-surface text-sm">{{ $user['nome'] }}</span>
                                        <span class="text-on-surface-variant text-[11px]">{{ $user['email'] }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- CPF -->
                            <td class="py-3 px-4 font-mono font-medium text-on-surface">
                                {{ $user['cpf'] }}
                            </td>

                            <!-- Código de Acesso -->
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-lg bg-surface-container-high text-on-surface font-mono font-bold text-xs">
                                    {{ $user['codigo_operador'] }}
                                </span>
                            </td>

                            <!-- Perfil -->
                            <td class="py-3 px-4">
                                @if ($user['perfil'] === 'admin')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-purple-100 text-purple-900 font-bold text-[11px]">
                                        <span class="material-symbols-outlined text-[14px]">admin_panel_settings</span>
                                        <span>Administrador</span>
                                    </span>
                                @elseif ($user['perfil'] === 'supervisor')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-100 text-blue-900 font-bold text-[11px]">
                                        <span class="material-symbols-outlined text-[14px]">security</span>
                                        <span>Supervisor</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-900 font-bold text-[11px]">
                                        <span class="material-symbols-outlined text-[14px]">badge</span>
                                        <span>Operador</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                @if ($user['ativo'])
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-[11px] font-bold border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        <span>Ativo</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-50 text-red-800 text-[11px] font-bold border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                        <span>Inativo</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Data Cadastro -->
                            <td class="py-3 px-4 text-on-surface-variant font-mono">
                                {{ $user['created_at'] }}
                            </td>

                            <!-- Ações -->
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button 
                                        type="button" 
                                        wire:click="openEditModal({{ $user['id'] }})"
                                        class="h-8 px-2.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold flex items-center gap-1 transition-colors cursor-pointer"
                                        title="Editar dados do usuário"
                                    >
                                        <span class="material-symbols-outlined text-[15px]">edit</span>
                                        <span>Editar</span>
                                    </button>

                                    <button 
                                        type="button" 
                                        wire:click="toggleStatus({{ $user['id'] }})"
                                        class="h-8 px-2.5 rounded-lg {{ $user['ativo'] ? 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200' }} text-xs font-bold flex items-center gap-1 transition-colors cursor-pointer"
                                        title="{{ $user['ativo'] ? 'Desativar acesso' : 'Reativar acesso' }}"
                                    >
                                        <span class="material-symbols-outlined text-[15px]">
                                            {{ $user['ativo'] ? 'block' : 'check_circle' }}
                                        </span>
                                        <span>{{ $user['ativo'] ? 'Desativar' : 'Ativar' }}</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[36px] text-on-surface-variant/60 mb-2">person_off</span>
                                <p class="text-sm font-semibold">Nenhum usuário encontrado com os filtros selecionados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL DE CADASTRO / EDIÇÃO DE USUÁRIO -->
    @if ($showFormModal)
        <x-modal 
            title="{{ $editingId ? 'Editar Usuário do Sistema' : 'Novo Usuário do Sistema' }}"
            subtitle="Defina os dados cadastrais, perfil de acesso e credenciais de login"
            icon="{{ $editingId ? 'edit' : 'person_add' }}"
            onClose="closeModal"
            maxWidth="xl"
        >
            <form wire:submit.prevent="save" class="flex flex-col gap-4">
                <!-- Nome Completo -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-on-surface" for="nome">Nome Completo *</label>
                    <input 
                        wire:model="nome"
                        id="nome"
                        type="text" 
                        placeholder="Ex: Carlos Eduardo da Silva"
                        class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary @error('nome') border-red-500 @enderror"
                    />
                    @error('nome')
                        <span class="text-red-600 text-[11px] font-semibold">{{ $message }}</span>
                    @enderror
                </div>

                <!-- CPF & Código de Operador -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-bold text-on-surface" for="cpf">CPF *</label>
                        <input 
                            wire:model="cpf"
                            id="cpf"
                            type="text" 
                            placeholder="000.000.000-00"
                            class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-mono font-semibold rounded-xl focus:outline-none focus:border-primary @error('cpf') border-red-500 @enderror"
                        />
                        @error('cpf')
                            <span class="text-red-600 text-[11px] font-semibold">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-bold text-on-surface" for="codigo_operador">Código de Acesso / Matrícula *</label>
                        <input 
                            wire:model="codigo_operador"
                            id="codigo_operador"
                            type="text" 
                            placeholder="Ex: GDA-104 ou ADM-001"
                            class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-mono font-bold uppercase rounded-xl focus:outline-none focus:border-primary @error('codigo_operador') border-red-500 @enderror"
                        />
                        @error('codigo_operador')
                            <span class="text-red-600 text-[11px] font-semibold">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- E-mail & Perfil -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-bold text-on-surface" for="email">E-mail Institucional *</label>
                        <input 
                            wire:model="email"
                            id="email"
                            type="email" 
                            placeholder="usuario@fatec.sp.gov.br"
                            class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary @error('email') border-red-500 @enderror"
                        />
                        @error('email')
                            <span class="text-red-600 text-[11px] font-semibold">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-bold text-on-surface" for="perfil">Perfil de Acesso *</label>
                        <select 
                            wire:model="perfil"
                            id="perfil"
                            class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer"
                        >
                            <option value="operador">Operador de Portaria (Guarita)</option>
                            <option value="supervisor">Supervisor de Segurança</option>
                            <option value="admin">Administrador Geral</option>
                        </select>
                        @error('perfil')
                            <span class="text-red-600 text-[11px] font-semibold">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Senhas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2 border-t border-surface-container">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-bold text-on-surface" for="senha">
                            {{ $editingId ? 'Nova Senha (opcional)' : 'Senha de Acesso *' }}
                        </label>
                        <input 
                            wire:model="senha"
                            id="senha"
                            type="password" 
                            placeholder="Mínimo 6 caracteres"
                            class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs rounded-xl focus:outline-none focus:border-primary @error('senha') border-red-500 @enderror"
                        />
                        @error('senha')
                            <span class="text-red-600 text-[11px] font-semibold">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-bold text-on-surface" for="senha_confirmation">Confirmar Senha</label>
                        <input 
                            wire:model="senha_confirmation"
                            id="senha_confirmation"
                            type="password" 
                            placeholder="Repita a senha"
                            class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs rounded-xl focus:outline-none focus:border-primary"
                        />
                    </div>
                </div>

                <!-- Status Ativo -->
                <div class="flex items-center gap-2 pt-2">
                    <input 
                        wire:model="ativo" 
                        type="checkbox" 
                        id="ativo" 
                        class="w-4 h-4 rounded text-primary focus:ring-primary border-surface-container-highest cursor-pointer"
                    />
                    <label for="ativo" class="text-xs font-bold text-on-surface cursor-pointer">
                        Usuário Ativo (Pode realizar login no sistema)
                    </label>
                </div>
            </form>

            <x-slot:footer>
                <button 
                    type="button" 
                    wire:click="closeModal"
                    class="px-4 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold transition-colors cursor-pointer"
                >
                    Cancelar
                </button>
                <button 
                    type="button" 
                    wire:click="save"
                    class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    <span>Salvar Usuário</span>
                </button>
            </x-slot:footer>
        </x-modal>
    @endif
</div>
