<?php

namespace App\Livewire\Admin;

use App\Enums\PerfilUsuario;
use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property-read LengthAwarePaginator $usuarios
 * @property-read array{total: int, admins: int, operadores: int, supervisores: int} $stats
 */
#[Layout('layouts.app')]
#[Title('Admin - Gestão de Usuários do Sistema - Sentinela FATEC')]
class Usuarios extends Component
{
    use WithPagination;

    public string $search = '';

    public string $perfilFilter = 'todos';

    public string $statusFilter = 'todos';

    public bool $showFormModal = false;

    public ?int $editingId = null;

    // Campos do Formulário
    public string $nome = '';

    public string $cpf = '';

    public string $email = '';

    public string $perfil = PerfilUsuario::Operador->value;

    public string $codigo_operador = '';

    public string $senha = '';

    public string $senha_confirmation = '';

    public bool $ativo = true;

    // Feedback visual
    public string $toastMessage = '';

    public string $toastType = 'success';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerfilFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->perfil = PerfilUsuario::Operador->value;
        $this->codigo_operador = Usuario::gerarProximoCodigo($this->perfil);
        $this->showFormModal = true;
    }

    public function updatedPerfil(string $value): void
    {
        if ($this->editingId === null) {
            $this->codigo_operador = Usuario::gerarProximoCodigo($value);
        }
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $this->editingId = $id;

        $user = Usuario::find($id);
        if ($user) {
            $this->nome = $user->nome;
            $this->cpf = $user->cpf;
            $this->email = $user->email;
            $this->perfil = $user->perfil;
            $this->codigo_operador = $user->codigo_operador;
            $this->ativo = (bool) $user->ativo;
            $this->showFormModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $rules = [
            'nome' => ['required', 'min:3', 'max:100'],
            'cpf' => [
                'required',
                'min:11',
                'max:14',
                Rule::unique('usuarios', 'cpf')->ignore($this->editingId),
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('usuarios', 'email')->ignore($this->editingId),
            ],
            'perfil' => ['required', Rule::in(PerfilUsuario::values())],
        ];

        if ($this->editingId === null) {
            $rules['senha'] = ['required', 'min:6', 'confirmed'];
            $this->codigo_operador = Usuario::gerarProximoCodigo($this->perfil);
        } elseif (! empty($this->senha)) {
            $rules['senha'] = ['min:6', 'confirmed'];
        }

        $messages = [
            'nome.required' => 'O nome do usuário é obrigatório.',
            'nome.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'cpf.required' => 'Informe o CPF do operador.',
            'cpf.unique' => 'Este CPF já está cadastrado para outro operador no sistema.',
            'email.required' => 'Informe o e-mail de acesso.',
            'email.email' => 'Informe um formato de e-mail válido.',
            'email.unique' => 'Este e-mail já está sendo utilizado.',
            'perfil.required' => 'Selecione o perfil de acesso.',
            'senha.required' => 'Defina uma senha de acesso.',
            'senha.min' => 'A senha deve possuir pelo menos 6 caracteres.',
            'senha.confirmed' => 'A confirmação de senha não confere.',
        ];

        $this->validate($rules, $messages);

        if ($this->editingId === null) {
            $user = Usuario::create([
                'nome' => $this->nome,
                'cpf' => $this->cpf,
                'email' => $this->email,
                'perfil' => $this->perfil,
                'codigo_operador' => strtoupper($this->codigo_operador),
                'senha' => $this->senha,
                'ativo' => $this->ativo,
            ]);

            $this->triggerToast("Novo usuário {$user->nome} ({$user->codigo_operador}) cadastrado com sucesso!", 'success');
        } else {
            $user = Usuario::findOrFail($this->editingId);
            $data = [
                'nome' => $this->nome,
                'cpf' => $this->cpf,
                'email' => $this->email,
                'perfil' => $this->perfil,
                'ativo' => $this->ativo,
            ];
            if (! empty($this->senha)) {
                $data['senha'] = $this->senha;
            }
            $user->update($data);

            $this->triggerToast('Dados do usuário atualizados com sucesso!', 'success');
        }

        unset($this->usuarios, $this->stats);
        $this->closeModal();
    }

    public function toggleStatus(int $id): void
    {
        $user = Usuario::findOrFail($id);
        $novoStatus = ! $user->ativo;
        $user->update(['ativo' => $novoStatus]);

        $statusMsg = $novoStatus ? 'ativado' : 'desativado';
        $tipoToast = $novoStatus ? 'success' : 'info';
        unset($this->usuarios);
        $this->triggerToast("Usuário {$user->nome} foi {$statusMsg}!", $tipoToast);
    }

    public function delete(int $id): void
    {
        if (Auth::id() === $id) {
            $this->triggerToast('Você não pode excluir seu próprio usuário logado!', 'error');

            return;
        }

        $user = Usuario::findOrFail($id);
        $nome = $user->nome;
        $user->delete();

        unset($this->usuarios, $this->stats);
        $this->triggerToast("Usuário {$nome} excluído com sucesso!", 'info');
    }

    private function resetForm(): void
    {
        $this->nome = '';
        $this->cpf = '';
        $this->email = '';
        $this->perfil = PerfilUsuario::Operador->value;
        $this->codigo_operador = '';
        $this->senha = '';
        $this->senha_confirmation = '';
        $this->ativo = true;
        $this->resetErrorBag();
    }

    /**
     * @return array{total: int, admins: int, operadores: int, supervisores: int}
     */
    #[Computed]
    public function stats(): array
    {
        $counts = Usuario::selectRaw("
            COUNT(*) as total,
            COALESCE(SUM(CASE WHEN perfil = 'admin' THEN 1 ELSE 0 END), 0) as admins,
            COALESCE(SUM(CASE WHEN perfil = 'operador' THEN 1 ELSE 0 END), 0) as operadores,
            COALESCE(SUM(CASE WHEN perfil = 'supervisor' THEN 1 ELSE 0 END), 0) as supervisores
        ")->first();

        return [
            'total' => (int) ($counts->total ?? 0),
            'admins' => (int) ($counts->admins ?? 0),
            'operadores' => (int) ($counts->operadores ?? 0),
            'supervisores' => (int) ($counts->supervisores ?? 0),
        ];
    }

    #[Computed]
    public function usuarios(): LengthAwarePaginator
    {
        return Usuario::query()
            ->select(['id', 'nome', 'email', 'cpf', 'codigo_operador', 'perfil', 'ativo', 'created_at'])
            ->filtrar($this->search, $this->perfilFilter, $this->statusFilter)
            ->orderByDesc('id')
            ->paginate(10);
    }

    private function triggerToast(string $message, string $type = 'success'): void
    {
        $this->toastMessage = $message;
        $this->toastType = $type;
    }

    public function clearToast(): void
    {
        $this->toastMessage = '';
    }

    public function render()
    {
        return view('livewire.admin.usuarios', [
            'usuariosList' => $this->usuarios,
            'stats' => $this->stats,
        ]);
    }
}
