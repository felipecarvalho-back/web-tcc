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

                <!-- Form -->
                <form wire:submit="login" class="flex flex-col gap-4">
                    <!-- Seletor de Perfil / Área de Entrada -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-on-surface">Área de Acesso</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button 
                                type="button" 
                                wire:click="$set('profile', 'guarita'); $set('accessCode', 'GDA-104')"
                                class="py-2.5 px-3 rounded-xl border text-xs font-bold flex items-center justify-center gap-2 transition-all cursor-pointer {{ $profile === 'guarita' ? 'bg-primary text-white border-primary shadow-xs' : 'bg-surface-container-low border-surface-container text-on-surface-variant hover:bg-surface-container' }}"
                            >
                                <span class="material-symbols-outlined text-[18px]">security</span>
                                <span>Guarita</span>
                            </button>
                            <button 
                                type="button" 
                                wire:click="$set('profile', 'admin'); $set('accessCode', 'ADM-001')"
                                class="py-2.5 px-3 rounded-xl border text-xs font-bold flex items-center justify-center gap-2 transition-all cursor-pointer {{ $profile === 'admin' ? 'bg-primary text-white border-primary shadow-xs' : 'bg-surface-container-low border-surface-container text-on-surface-variant hover:bg-surface-container' }}"
                            >
                                <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                                <span>Administrativo</span>
                            </button>
                        </div>
                    </div>

                    <!-- Código de Acesso (Sem e-mail) -->
                    <div class="flex flex-col gap-1.5">
                        <label for="accessCode" class="text-xs font-bold text-on-surface flex items-center justify-between">
                            <span>Código de Acesso do Operador</span>
                            <span class="text-on-surface-variant font-mono text-[11px]">Ex: GDA-104 ou ADM-001</span>
                        </label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">pin</span>
                            <input 
                                wire:model="accessCode" 
                                id="accessCode" 
                                type="text" 
                                required 
                                placeholder="Insira o seu código"
                                class="w-full h-11 pl-10 pr-4 bg-surface-container-lowest border border-surface-container-highest/80 text-on-surface font-mono font-bold text-sm uppercase rounded-xl focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors"
                            />
                        </div>
                    </div>

                    <!-- Senha / PIN -->
                    <div class="flex flex-col gap-1.5" x-data="{ showPassword: false }">
                        <div class="flex items-center justify-between">
                            <label for="password" class="text-xs font-bold text-on-surface">Senha / PIN</label>
                        </div>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">lock</span>
                            <input 
                                wire:model="password" 
                                id="password" 
                                :type="showPassword ? 'text' : 'password'" 
                                required 
                                placeholder="Insira sua senha ou PIN"
                                class="w-full h-11 pl-10 pr-11 bg-surface-container-lowest border border-surface-container-highest/80 text-on-surface text-sm rounded-xl focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors"
                            />
                            <button 
                                type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute right-2 p-1.5 text-on-surface-variant hover:text-on-surface rounded-lg transition-colors cursor-pointer"
                            >
                                <span class="material-symbols-outlined text-[20px]" x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
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
                            <span class="text-xs text-on-surface-variant font-medium">Manter credenciais neste terminal</span>
                        </label>
                    </div>

                    <!-- Botão de Acesso -->
                    <button 
                        type="submit" 
                        class="w-full h-12 mt-2 bg-primary hover:bg-primary-container text-white font-bold text-sm rounded-xl flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-all active:scale-[0.99] cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[20px]">login</span>
                        <span>Acessar Terminal</span>
                    </button>
                </form>

                <!-- Atalhos Diretos -->
                <div class="mt-8 pt-6 border-t border-surface-container flex flex-col gap-2.5">
                    <span class="text-[11px] font-bold text-on-surface-variant uppercase text-center tracking-wider">
                        Acesso Direto
                    </span>
                    <div class="grid grid-cols-2 gap-2">
                        <a 
                            href="{{ route('portaria.monitoramento') }}" 
                            class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg bg-surface-container hover:bg-surface-container-high text-xs font-semibold text-on-surface transition-colors cursor-pointer"
                        >
                            <span class="material-symbols-outlined text-[16px] text-primary">security</span>
                            <span>Entrar na Guarita</span>
                        </a>
                        <a 
                            href="{{ route('admin.dashboard') }}" 
                            class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg bg-surface-container hover:bg-surface-container-high text-xs font-semibold text-on-surface transition-colors cursor-pointer"
                        >
                            <span class="material-symbols-outlined text-[16px] text-primary">admin_panel_settings</span>
                            <span>Entrar no Admin</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="mt-6 text-center text-xs text-on-surface-variant">
            Sentinela FATEC • Autenticação por Código • Centro Paula Souza
        </p>
    </div>
</div>
