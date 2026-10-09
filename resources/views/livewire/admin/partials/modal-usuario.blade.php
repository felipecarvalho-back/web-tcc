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

            <!-- CPF com máscara -->
            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold text-on-surface" for="cpf">CPF *</label>
                <input 
                    wire:model="cpf" 
                    id="cpf" 
                    type="text" 
                    x-mask="999.999.999-99"
                    placeholder="000.000.000-00" 
                    class="h-10 px-3 bg-surface-container-low border border-surface-container-highest text-on-surface text-xs font-mono font-semibold rounded-xl focus:outline-none focus:border-primary @error('cpf') border-red-500 @enderror" 
                />
                @error('cpf')
                    <span class="text-red-600 text-[11px] font-semibold">{{ $message }}</span>
                @enderror
            </div>

            <!-- Código de Operador (Embaixo do CPF) -->
            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-on-surface" for="codigo_operador">Código de Acesso / Matrícula</label>
                    <span class="text-[10px] font-bold text-primary bg-primary/10 px-1.5 py-0.5 rounded">Gerado automaticamente</span>
                </div>
                <div class="relative">
                    <input 
                        wire:model="codigo_operador" 
                        id="codigo_operador" 
                        type="text"
                        readonly
                        tabindex="-1"
                        class="w-full h-10 px-3 bg-surface-container border border-surface-container-highest text-on-surface text-xs font-mono font-bold uppercase rounded-xl cursor-not-allowed select-none focus:outline-none" 
                    />
                    <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-on-surface-variant text-[18px]">lock</span>
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
                        wire:model.live="perfil" 
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
                wire:loading.attr="disabled"
                class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container disabled:opacity-50 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span>Salvar Usuário</span>
            </button>
        </x-slot:footer>
    </x-modal>
@endif
