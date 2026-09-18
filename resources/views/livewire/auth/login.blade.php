<div class="relative w-full min-h-screen flex items-center justify-center p-4 sm:p-8 overflow-hidden bg-background">
    <!-- Ambient Gradient Backing -->
    <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-primary-container/15 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-secondary-container/10 blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md z-10 flex flex-col items-center">
        <!-- Top Institutional Header Badge -->
        <div class="flex items-center gap-2 mb-6 px-4 py-1.5 rounded-full bg-surface-container shadow-xs border border-surface-container-highest/60">
            <span class="inline-block w-2 h-2 rounded-full bg-primary-container"></span>
            <span class="text-xs font-bold text-primary uppercase tracking-wider">Centro Paula Souza • Governo de SP</span>
        </div>

        <!-- Main Authentication Card -->
        <div class="w-full bg-surface-container-lowest rounded-2xl shadow-xl border border-surface-container overflow-hidden flex flex-col">
            <!-- Decorative Banner Gradient -->
            <div class="h-2 w-full bg-gradient-to-r from-primary via-primary-container to-secondary-container"></div>

            <div class="p-6 sm:p-8 flex flex-col">
                <!-- Header Brand Identity -->
                <div class="flex items-center gap-3.5 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-primary flex items-center justify-center text-white shadow-md shrink-0">
                        <span class="material-symbols-outlined text-[28px]">shield_person</span>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-extrabold text-on-surface tracking-tight">Sentinela</h1>
                            <span class="px-2 py-0.5 rounded text-white bg-primary-container text-xs font-bold">FATEC</span>
                        </div>
                        <p class="text-xs text-on-surface-variant mt-0.5">Controle de Acesso e Portaria</p>
                    </div>
                </div>

                <!-- Form -->
                <form wire:submit="login" class="flex flex-col gap-4">
                    <!-- Código de Acesso -->
                    <div class="flex flex-col gap-1.5">
                        <label for="accessCode" class="text-xs font-bold text-on-surface flex items-center justify-between">
                            <span>Código de Acesso</span>
                            <span class="text-on-surface-variant font-mono text-[11px]">Ex: GDA-104 ou ADM-001</span>
                        </label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">pin</span>
                            <input 
                                wire:model="accessCode" 
                                id="accessCode" 
                                type="text" 
                                required 
                                placeholder="Insira seu código"
                                class="w-full h-11 pl-10 pr-4 bg-surface-container-lowest border border-surface-container-highest/80 text-on-surface font-mono font-bold text-sm uppercase rounded-xl focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors"
                            />
                        </div>
                    </div>

                    <!-- Senha / PIN -->
                    <div class="flex flex-col gap-1.5" x-data="{ showPassword: false }">
                        <label for="password" class="text-xs font-bold text-on-surface">Senha / PIN</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">lock</span>
                            <input 
                                wire:model="password" 
                                id="password" 
                                :type="showPassword ? 'text' : 'password'" 
                                required 
                                placeholder="Insira sua senha"
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
            </div>
        </div>

        <!-- Footer -->
        <p class="mt-6 text-center text-xs text-on-surface-variant">
            Sentinela FATEC • Centro Paula Souza
        </p>
    </div>
</div>
