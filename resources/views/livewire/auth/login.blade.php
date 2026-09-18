<div class="relative w-full min-h-screen flex items-center justify-center p-4 sm:p-8 overflow-hidden bg-background">
    <!-- Ambient Gradient Backing -->
    <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-primary-container/15 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-secondary-container/10 blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-lg z-10 flex flex-col items-center">
        <!-- Top Institutional Header Badge -->
        <div class="flex items-center gap-2 mb-6 px-4 py-1.5 rounded-full bg-surface-container shadow-xs border border-surface-container-highest/60">
            <span class="inline-block w-2 h-2 rounded-full bg-primary-container animate-pulse"></span>
            <span class="text-xs font-bold text-primary uppercase tracking-wider">Centro Paula Souza • Governo de SP</span>
        </div>

        <!-- Main Authentication Card -->
        <div class="w-full bg-surface-container-lowest rounded-2xl shadow-xl border border-surface-container overflow-hidden flex flex-col">
            <!-- Decorative Banner Gradient -->
            <div class="h-2.5 w-full bg-gradient-to-r from-primary via-primary-container to-secondary-container"></div>

            <div class="p-6 sm:p-10 flex flex-col">
                <!-- Header Brand Identity -->
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-primary flex items-center justify-center text-white shadow-md">
                            <span class="material-symbols-outlined text-[28px]">shield_person</span>
                        </div>
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <h1 class="text-2xl font-extrabold text-on-surface tracking-tight">Sentinela</h1>
                                <span class="px-2 py-0.5 rounded text-white bg-primary-container text-xs font-bold">FATEC</span>
                            </div>
                            <p class="text-xs text-on-surface-variant mt-0.5">Sistema Integrado de Controle de Acesso e Portaria</p>
                        </div>
                    </div>

                    <div class="hidden sm:flex flex-col items-end">
                        <span class="inline-flex items-center gap-1.5 text-xs text-on-surface-variant bg-surface-container-high px-2.5 py-1 rounded-md border border-surface-container">
                            <span class="material-symbols-outlined text-[14px] text-primary">sensors</span>
                            Reverb Online
                        </span>
                    </div>
                </div>

                <!-- Operational Notice -->
                <div class="bg-surface-container-low border border-surface-container/80 rounded-xl p-3.5 mb-6 flex items-start gap-3">
                    <span class="material-symbols-outlined text-primary text-[20px] shrink-0 mt-0.5">verified_user</span>
                    <p class="text-xs text-on-surface leading-relaxed">
                        Autenticação restrita e monitorada para operadores de guarita e gestão predial conforme a política de segurança FATEC/CPS.
                    </p>
                </div>

                <!-- Form -->
                <form wire:submit="login" class="flex flex-col gap-4">
                    <!-- Usuário / Matrícula -->
                    <div class="flex flex-col gap-1.5">
                        <label for="identifier" class="text-xs font-bold text-on-surface flex items-center justify-between">
                            <span>Usuário / Matrícula ou E-mail</span>
                            <span class="text-on-surface-variant font-normal">@fatec.sp.gov.br</span>
                        </label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">badge</span>
                            <input 
                                wire:model="identifier" 
                                id="identifier" 
                                type="text" 
                                required 
                                placeholder="Ex: operador.silva@fatec.sp.gov.br"
                                class="w-full h-11 pl-10 pr-4 bg-surface-container-lowest border border-surface-container-highest/80 text-on-surface text-sm rounded-xl focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors"
                            />
                        </div>
                    </div>

                    <!-- Senha -->
                    <div class="flex flex-col gap-1.5" x-data="{ showPassword: false }">
                        <div class="flex items-center justify-between">
                            <label for="password" class="text-xs font-bold text-on-surface">Senha de Acesso</label>
                            <a href="#" class="text-xs text-primary hover:underline font-semibold">
                                Esqueci minha senha
                            </a>
                        </div>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">lock</span>
                            <input 
                                wire:model="password" 
                                id="password" 
                                :type="showPassword ? 'text' : 'password'" 
                                required 
                                placeholder="Insira sua senha de acesso"
                                class="w-full h-11 pl-10 pr-11 bg-surface-container-lowest border border-surface-container-highest/80 text-on-surface text-sm rounded-xl focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors"
                            />
                            <button 
                                type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute right-2 p-1.5 text-on-surface-variant hover:text-on-surface rounded-lg transition-colors"
                            >
                                <span class="material-symbols-outlined text-[20px]" x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                    </div>

                    <!-- Posto Operacional -->
                    <div class="flex flex-col gap-1.5">
                        <label for="workstation" class="text-xs font-bold text-on-surface">
                            Posto Operacional de Alocação
                        </label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">storefront</span>
                            <select 
                                wire:model="workstation" 
                                id="workstation" 
                                class="w-full h-11 pl-10 pr-10 bg-surface-container-lowest border border-surface-container-highest/80 text-on-surface text-sm rounded-xl focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors appearance-none cursor-pointer"
                            >
                                <option value="principal">Guarita Principal (Entrada/Saída de Veículos)</option>
                                <option value="bloco_adm">Guarita Bloco Administrativo • Vagas Docentes</option>
                                <option value="central_admin">Central de Monitoramento • Painel Geral (Admin)</option>
                            </select>
                            <span class="material-symbols-outlined absolute right-3 pointer-events-none text-on-surface-variant text-[20px]">expand_more</span>
                        </div>
                    </div>

                    <!-- Lembrar sessão -->
                    <div class="flex items-center justify-between mt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input 
                                wire:model="remember" 
                                type="checkbox" 
                                class="w-4 h-4 rounded text-primary focus:ring-primary border-surface-container-highest cursor-pointer"
                            />
                            <span class="text-xs text-on-surface-variant font-medium">Manter sessão ativa neste terminal</span>
                        </label>
                    </div>

                    <!-- Botão de Login -->
                    <button 
                        type="submit" 
                        class="w-full h-12 mt-2 bg-primary hover:bg-primary-container text-white font-bold text-sm rounded-xl flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-all active:scale-[0.99] cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[20px]">login</span>
                        <span>Acessar Terminal de Controle</span>
                    </button>
                </form>

                <!-- Atalhos Rápidos para Demonstração de Frontend -->
                <div class="mt-8 pt-6 border-t border-surface-container flex flex-col gap-2.5">
                    <span class="text-[11px] font-bold text-on-surface-variant uppercase text-center tracking-wider">
                        Acesso Rápido de Demonstração
                    </span>
                    <div class="grid grid-cols-2 gap-2">
                        <a 
                            href="{{ route('portaria.monitoramento') }}" 
                            class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg bg-surface-container hover:bg-surface-container-high text-xs font-semibold text-on-surface transition-colors"
                        >
                            <span class="material-symbols-outlined text-[16px] text-primary">security</span>
                            <span>Módulo Guarda</span>
                        </a>
                        <a 
                            href="{{ route('admin.dashboard') }}" 
                            class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg bg-surface-container hover:bg-surface-container-high text-xs font-semibold text-on-surface transition-colors"
                        >
                            <span class="material-symbols-outlined text-[16px] text-primary">admin_panel_settings</span>
                            <span>Módulo Admin</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="mt-6 text-center text-xs text-on-surface-variant">
            Sentinela FATEC • Versão 2.4.0-Livewire • Centro Paula Souza
        </p>
    </div>
</div>
