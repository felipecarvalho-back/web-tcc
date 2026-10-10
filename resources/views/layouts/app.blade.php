<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Sentinela FATEC - Controle de Acesso e Portaria' }}</title>
    
    <!-- Google Fonts: Plus Jakarta Sans & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-background text-on-surface font-sans min-h-screen antialiased selection:bg-primary-container selection:text-white" x-data="{ mobileMenuOpen: false }">

    @php
        $isAdminArea = request()->is('admin*');
    @endphp

    <!-- SIDEBAR PERSISTENTE -->
    <aside 
        class="fixed inset-y-0 left-0 z-30 w-64 bg-surface-container-lowest border-r border-surface-container shadow-xs flex flex-col justify-between transition-transform duration-300 md:translate-x-0 no-print"
        :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >
        <div class="p-4 flex flex-col gap-5 overflow-y-auto">
            <!-- Brand & Identidade -->
            <div class="flex items-center justify-between">
                <a href="{{ $isAdminArea ? route('admin.dashboard') : route('portaria.monitoramento') }}" wire:navigate class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center text-white shadow-md group-hover:scale-105 transition-transform shrink-0">
                        <span class="material-symbols-outlined text-[22px]">shield_person</span>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-1.5">
                            <span class="text-lg font-bold text-primary tracking-tight">Sentinela</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold uppercase bg-primary-container text-white">FATEC</span>
                        </div>
                        <span class="text-[11px] text-on-surface-variant font-medium tracking-wide">
                            {{ $isAdminArea ? 'Painel Administrativo' : 'Terminal da Guarita' }}
                        </span>
                    </div>
                </a>

                <!-- Fechar menu mobile -->
                <button 
                    type="button" 
                    class="md:hidden p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container cursor-pointer"
                    @click="mobileMenuOpen = false"
                >
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <!-- Navegação -->
            <nav class="flex flex-col gap-4">
                @if (!$isAdminArea)
                    <!-- MENU DA GUARITA -->
                    <div class="flex flex-col gap-1">
                        <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider px-3 mb-1">
                            Operação da Guarita
                        </span>
                        
                        <a 
                            href="{{ route('portaria.monitoramento') }}"
                            wire:navigate
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('portaria.monitoramento') ? 'bg-primary-container text-white shadow-xs' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}"
                        >
                            <span class="material-symbols-outlined text-[19px]">videocam</span>
                            <span>Início / Monitoramento</span>
                        </a>

                        <a 
                            href="{{ route('portaria.visitantes') }}"
                            wire:navigate
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('portaria.visitantes') ? 'bg-primary-container text-white shadow-xs' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}"
                        >
                            <span class="material-symbols-outlined text-[19px]">badge</span>
                            <span>Cadastro de Visitantes</span>
                        </a>
                    </div>
                @else
                    <!-- MENU ADMINISTRATIVO -->
                    <div class="flex flex-col gap-1">
                        <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider px-3 mb-1">
                            Administração
                        </span>

                        <a 
                            href="{{ route('admin.dashboard') }}"
                            wire:navigate
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-primary-container text-white shadow-xs' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}"
                        >
                            <span class="material-symbols-outlined text-[19px]">analytics</span>
                            <span>Dashboard & Indicadores</span>
                        </a>

                        <a 
                            href="{{ route('admin.usuarios') }}"
                            wire:navigate
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('admin.usuarios') ? 'bg-primary-container text-white shadow-xs' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}"
                        >
                            <span class="material-symbols-outlined text-[19px]">manage_accounts</span>
                            <span>Usuários do Sistema</span>
                        </a>

                        <a 
                            href="{{ route('admin.condutores') }}"
                            wire:navigate
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('admin.condutores') ? 'bg-primary-container text-white shadow-xs' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}"
                        >
                            <span class="material-symbols-outlined text-[19px]">group</span>
                            <span>Gestão de Condutores</span>
                        </a>

                        <a 
                            href="{{ route('admin.relatorios') }}"
                            wire:navigate
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('admin.relatorios') ? 'bg-primary-container text-white shadow-xs' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}"
                        >
                            <span class="material-symbols-outlined text-[19px]">description</span>
                            <span>Relatórios & Exportação</span>
                        </a>
                    </div>
                @endif
            </nav>
        </div>

        <!-- Footer do Menu: Usuário & Logout -->
        <div class="p-3.5 border-t border-surface-container bg-surface-container-lowest flex flex-col gap-2.5">
            <div class="flex items-center gap-2.5 p-2 bg-surface-container-low rounded-xl border border-surface-container/60">
                <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm shrink-0">
                    <span class="material-symbols-outlined text-[20px]">
                        {{ $isAdminArea ? 'admin_panel_settings' : 'security' }}
                    </span>
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-xs font-bold text-on-surface truncate">
    {{ auth()->user()->nome }}
</span>
<span class="text-[11px] text-on-surface-variant truncate font-mono">
    Código: {{ auth()->user()->codigo_operador }}
</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button 
        type="submit"
        class="w-full flex items-center justify-center gap-1.5 py-2 px-3 bg-surface-container hover:bg-error-container hover:text-error text-on-surface font-semibold text-xs rounded-lg transition-colors cursor-pointer"
    >
        <span class="material-symbols-outlined text-[17px]">logout</span>
        <span>Sair do Sistema</span>
    </button>
</form>
        </div>
    </aside>

    <!-- BACKDROP MOBILE -->
    <div 
        x-show="mobileMenuOpen" 
        x-cloak 
        @click="mobileMenuOpen = false" 
        class="fixed inset-0 z-30 bg-black/40 backdrop-blur-xs md:hidden"
    ></div>

    <!-- ÁREA PRINCIPAL -->
    <div class="md:pl-64 flex flex-col min-h-screen">
        <!-- TOPBAR -->
        <header class="sticky top-0 z-20 h-16 bg-surface/90 backdrop-blur-md border-b border-surface-container flex items-center justify-between px-4 sm:px-8 no-print">
            <div class="flex items-center gap-3">
                <button 
                    type="button" 
                    class="md:hidden p-2 rounded-lg text-on-surface-variant hover:bg-surface-container cursor-pointer"
                    @click="mobileMenuOpen = true"
                >
                    <span class="material-symbols-outlined text-[22px]">menu</span>
                </button>

                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider {{ $isAdminArea ? 'bg-purple-100 text-purple-900 border border-purple-200' : 'bg-primary/10 text-primary border border-primary/20' }}">
                        {{ $isAdminArea ? 'Área Administrativa' : 'Área da Guarita' }}
                    </span>
                    <span class="hidden lg:inline text-on-surface-variant text-xs font-semibold">
                        Centro Paula Souza • Faculdade de Tecnologia (FATEC)
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-3 sm:gap-6">
                <!-- Status da Cancela -->
                <div class="hidden lg:flex items-center gap-2 px-3 py-1 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Cancela 01:</span>
                    <span class="font-bold">OPERACIONAL</span>
                </div>

                <!-- Relógio -->
                <div 
                    x-data="{ time: '' }" 
                    x-init="setInterval(() => { const d = new Date(); time = d.toLocaleTimeString('pt-BR'); }, 1000); time = (new Date()).toLocaleTimeString('pt-BR');"
                    class="flex items-center gap-1.5 px-3 py-1 bg-surface-container-high rounded-lg text-xs font-bold text-on-surface font-mono"
                >
                    <span class="material-symbols-outlined text-[16px] text-primary">schedule</span>
                    <span x-text="time">12:00:00</span>
                </div>

                <!-- Badge de Código -->
                <div class="px-2.5 py-1 bg-primary text-white rounded-lg font-mono font-bold text-xs shadow-xs">
                    {{ auth()->user()->codigo_operador }}
                </div>
            </div>
        </header>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
