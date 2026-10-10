<div class="flex flex-col gap-6">
    <!-- CABEÇALHO -->
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-container-lowest p-6 rounded-2xl border border-surface-container shadow-xs">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <span
                    class="px-2 py-0.5 rounded bg-primary-container text-white text-[11px] font-bold uppercase tracking-wider">
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
                Gerenciamento de contas de acesso, porteiros da guarita, supervisores e administradores do Sentinela
                FATEC.
            </p>
        </div>

        <button type="button" wire:click="openCreateModal"
            class="px-5 py-3 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold flex items-center gap-2 shadow-md hover:shadow-lg transition-all active:scale-[0.99] cursor-pointer self-start sm:self-auto shrink-0">
            <span class="material-symbols-outlined text-[20px]">person_add</span>
            <span>Novo Usuário</span>
        </button>
    </div>

    <!-- CARDS DE RESUMO DE USUÁRIOS -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div
            class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col gap-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-on-surface-variant uppercase">Total Usuários</span>
                <span class="material-symbols-outlined text-primary text-[20px]">group</span>
            </div>
            <div class="text-2xl font-extrabold text-on-surface font-mono">{{ $stats['total'] }}</div>
            <span class="text-[11px] text-on-surface-variant">Cadastrados no banco</span>
        </div>

        <div
            class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col gap-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-on-surface-variant uppercase">Administradores</span>
                <span class="material-symbols-outlined text-purple-700 text-[20px]">admin_panel_settings</span>
            </div>
            <div class="text-2xl font-extrabold text-purple-900 font-mono">
                {{ $stats['admins'] }}
            </div>
            <span class="text-[11px] text-on-surface-variant">Acesso total</span>
        </div>

        <div
            class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col gap-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-on-surface-variant uppercase">Porteiros / Operadores</span>
                <span class="material-symbols-outlined text-emerald-700 text-[20px]">badge</span>
            </div>
            <div class="text-2xl font-extrabold text-emerald-800 font-mono">
                {{ $stats['operadores'] }}
            </div>
            <span class="text-[11px] text-on-surface-variant">Operam as guaritas</span>
        </div>

        <div
            class="p-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs flex flex-col gap-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-on-surface-variant uppercase">Supervisores</span>
                <span class="material-symbols-outlined text-blue-700 text-[20px]">security</span>
            </div>
            <div class="text-2xl font-extrabold text-blue-900 font-mono">
                {{ $stats['supervisores'] }}
            </div>
            <span class="text-[11px] text-on-surface-variant">Gestão do turno</span>
        </div>
    </div>


    <!-- PAINEL DE FILTROS & BUSCA -->
    <div
        class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container shadow-xs flex flex-col gap-4">
        <div class="flex items-center gap-2 pb-3 border-b border-surface-container">
            <span class="material-symbols-outlined text-primary text-[20px]">filter_alt</span>
            <h2 class="text-xs font-bold text-on-surface uppercase tracking-wider">Filtros de Pesquisa</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            <!-- Busca Geral -->
            <div class="flex flex-col gap-1 lg:col-span-2">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase">Buscar Usuário</label>
                <div class="relative flex items-center">
                    <span
                        class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[18px] pointer-events-none">search</span>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Nome, CPF, e-mail ou código de operador..."
                        class="w-full h-10 pl-10 pr-10 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary" />
                    <span wire:loading wire:target="search"
                        class="material-symbols-outlined absolute right-3 text-primary text-[18px] animate-spin">
                        progress_activity
                    </span>
                </div>
            </div>

            <!-- Filtro por Perfil -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase">Perfil de Acesso</label>
                <select wire:model.live="perfilFilter"
                    class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer">
                    <option value="todos">Todos os Perfis</option>
                    <option value="admin">Administrador Geral</option>
                    <option value="operador">Operador de Portaria</option>
                    <option value="supervisor">Supervisor de Segurança</option>
                </select>
            </div>

            <!-- Filtro por Status -->
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase">Status da Conta</label>
                <select wire:model.live="statusFilter"
                    class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-semibold rounded-xl focus:outline-none focus:border-primary cursor-pointer">
                    <option value="todos">Todos os Status</option>
                    <option value="ativo">Apenas Ativos</option>
                    <option value="inativo">Apenas Inativos</option>
                </select>
            </div>
        </div>
    </div>

    <!-- TABELA DE USUÁRIOS -->
    <div
        class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs overflow-hidden flex flex-col">
        <div class="p-4 bg-surface-container-low border-b border-surface-container flex items-center justify-between">
            <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider">
                Usuários Cadastrados
            </h3>
            <span class="text-xs text-on-surface-variant">
                Exibindo <strong class="text-on-surface">{{ $usuariosList->total() }}</strong> registro(s)
            </span>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-surface-container/60 text-on-surface-variant uppercase text-[11px] font-bold tracking-wider h-11 border-b border-surface-container">
                        <th class="py-2.5 px-4">Usuário</th>
                        <th class="py-2.5 px-4">Código de Acesso</th>
                        <th class="py-2.5 px-4">Perfil</th>
                        <th class="py-2.5 px-4">Status</th>
                        <th class="py-2.5 px-4">Data Cadastro</th>
                        <th class="py-2.5 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container text-xs">
                    @forelse ($usuariosList as $user)
                        <tr wire:key="user-{{ $user->id }}"
                            class="hover:bg-surface-container-low/60 transition-colors">
                            <!-- Nome & E-mail -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-full bg-primary/10 text-primary font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ $user->initials() }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-on-surface text-sm">{{ $user->nome }}</span>
                                        <span class="text-on-surface-variant text-[11px]">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Código de Acesso -->
                            <td class="py-3 px-4">
                                <span
                                    class="px-2.5 py-1 rounded-lg bg-surface-container-high text-on-surface font-mono font-bold text-xs">
                                    {{ $user->codigo_operador }}
                                </span>
                            </td>

                            <!-- Perfil -->
                            <td class="py-3 px-4">
                                @if ($user->perfil === 'admin')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-purple-100 text-purple-900 font-bold text-[11px]">
                                        <span class="material-symbols-outlined text-[14px]">admin_panel_settings</span>
                                        <span>Administrador</span>
                                    </span>
                                @elseif ($user->perfil === 'supervisor')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-100 text-blue-900 font-bold text-[11px]">
                                        <span class="material-symbols-outlined text-[14px]">security</span>
                                        <span>Supervisor</span>
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-900 font-bold text-[11px]">
                                        <span class="material-symbols-outlined text-[14px]">badge</span>
                                        <span>Operador</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                @if ($user->ativo)
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-[11px] font-bold border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        <span>Ativo</span>
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-50 text-red-800 text-[11px] font-bold border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                        <span>Inativo</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Data Cadastro -->
                            <td class="py-3 px-4 text-on-surface-variant font-mono">
                                {{ $user->created_at?->format('d/m/Y') }}
                            </td>

                            <!-- Ações -->
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" wire:click="openEditModal({{ $user->id }})"
                                        class="h-8 px-2.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold flex items-center gap-1 transition-colors cursor-pointer"
                                        title="Editar dados do usuário">
                                        <span class="material-symbols-outlined text-[15px]">edit</span>
                                        <span>Editar</span>
                                    </button>

                                    @if (auth()->id() !== $user->id)
                                        <button type="button" wire:click="toggleStatus({{ $user->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="toggleStatus({{ $user->id }})"
                                            class="h-8 px-2.5 rounded-lg {{ $user->ativo ? 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200' }} text-xs font-bold flex items-center gap-1 transition-colors cursor-pointer disabled:opacity-50"
                                            title="{{ $user->ativo ? 'Desativar acesso' : 'Reativar acesso' }}">
                                            <span class="material-symbols-outlined text-[15px]">
                                                {{ $user->ativo ? 'block' : 'check_circle' }}
                                            </span>
                                            <span>{{ $user->ativo ? 'Desativar' : 'Ativar' }}</span>
                                        </button>
                                    @endif

                                    @if (auth()->id() !== $user->id)
                                        <button type="button" wire:click="delete({{ $user->id }})"
                                            wire:confirm="Tem certeza de que deseja excluir o usuário {{ $user->nome }}? Esta ação enviará o usuário para a lixeira."
                                            wire:loading.attr="disabled" wire:target="delete({{ $user->id }})"
                                            class="h-8 px-2.5 rounded-lg bg-red-50 text-red-800 hover:bg-red-100 border border-red-200 text-xs font-bold flex items-center gap-1 transition-colors cursor-pointer disabled:opacity-50"
                                            title="Excluir usuário">
                                            <span class="material-symbols-outlined text-[15px]">delete</span>
                                            <span>Excluir</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-on-surface-variant">
                                <span
                                    class="material-symbols-outlined text-[36px] text-on-surface-variant/60 mb-2">person_off</span>
                                <p class="text-sm font-semibold">Nenhum usuário encontrado com os filtros selecionados.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($usuariosList->hasPages())
            <div class="p-4 bg-surface-container-low border-t border-surface-container">
                {{ $usuariosList->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL DE CADASTRO / EDIÇÃO DE USUÁRIO (COMPONENTE PARCIAL) -->
    @include('livewire.admin.partials.modal-usuario')

    <!-- TOAST NOTIFICATION -->
    <x-toast :message="$toastMessage" :type="$toastType" />
</div>
