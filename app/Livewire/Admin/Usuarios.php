<?php

namespace App\Livewire\Admin;

use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    public string $perfil = 'operador'; // admin, operador, supervisor

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
        $this->perfil = 'operador';
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
            'nome' => 'required|min:3|max:100',
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
            'perfil' => 'required|in:admin,operador,supervisor',
        ];

        if ($this->editingId === null) {
            $rules['senha'] = 'required|min:6|confirmed';
            $this->codigo_operador = Usuario::gerarProximoCodigo($this->perfil);
        } elseif (! empty($this->senha)) {
            $rules['senha'] = 'min:6|confirmed';
        }

        $this->validate($rules);

        if ($this->editingId === null) {
            $user = Usuario::create([
                'nome' => $this->nome,
                'cpf' => $this->cpf,
                'email' => $this->email,
                'perfil' => $this->perfil,
                'codigo_operador' => strtoupper($this->codigo_operador),
                'senha' => Hash::make($this->senha),
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
                $data['senha'] = Hash::make($this->senha);
            }
            $user->update($data);

            $this->triggerToast('Dados do usuário atualizados com sucesso!', 'success');
        }

        $this->closeModal();
    }

    public function toggleStatus(int $id): void
    {
        $user = Usuario::findOrFail($id);
        $novoStatus = ! $user->ativo;
        $user->update(['ativo' => $novoStatus]);

        $statusMsg = $novoStatus ? 'ativado' : 'desativado';
        $tipoToast = $novoStatus ? 'success' : 'info';
        $this->triggerToast("Usuário {$user->nome} foi {$statusMsg}!", $tipoToast);
    }

    public function delete(int $id): void
    {
        if (Auth::user() === $id) {
            $this->triggerToast('Você não pode excluir seu próprio usuário logado!', 'error');

            return;
        }

        $user = Usuario::findOrFail($id);
        $nome = $user->nome;
        $user->delete();

        $this->triggerToast("Usuário {$nome} excluído com sucesso!", 'info');
    }

    private function resetForm(): void
    {
        $this->nome = '';
        $this->cpf = '';
        $this->email = '';
        $this->perfil = 'operador';
        $this->codigo_operador = '';
        $this->senha = '';
        $this->senha_confirmation = '';
        $this->ativo = true;
        $this->resetErrorBag();
    }

    #[Computed]
    public function stats(): array
    {
        $counts = Usuario::selectRaw("
            count(*) as total,
            count(case when perfil = 'admin' then 1 end) as admins,
            count(case when perfil = 'operador' then 1 end) as operadores,
            count(case when perfil = 'supervisor' then 1 end) as supervisores
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
            ->select(['id', 'nome', 'email', 'cpf', 'codigo_operador', 'perfil', 'ativo', 'created_at']) // Busca apenas as colunas necessárias
            ->when(trim($this->search), function ($query, $term) { // Se digitou algo na busca:
                $query->where(function ($q) use ($term) {
                    $q->where('nome', 'like', "%{$term}%") // Busca por nome
                        ->orWhere('email', 'like', "%{$term}%") // ...ou por email
                        ->orWhere('cpf', 'like', "%{$term}%") // ...ou por CPF
                        ->orWhere('codigo_operador', 'like', "%{$term}%"); // ...ou por código de operador
                });
            })
            ->when($this->perfilFilter !== 'todos', function ($query) { // Se escolheu um perfil específico:
                $query->where('perfil', $this->perfilFilter); // Filtra pelo perfil
            })
            ->when($this->statusFilter === 'ativo', function ($query) { // Se o filtro for 'ativo':
                $query->where('ativo', true); // Traz apenas ativos
            })
            ->when($this->statusFilter === 'inativo', function ($query) { // Se o filtro for 'inativo':
                $query->where('ativo', false); // Traz apenas inativos
            })
            ->orderByDesc('id') // Mais recentes primeiro (ID decrescente)
            ->paginate(10); // Divide em páginas de 10 em 10
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
            'usuariosList' => $this->usuarios(),
            'stats' => $this->stats(),
        ]);
    }
}
