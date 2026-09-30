<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Admin - Gestão de Usuários do Sistema - Sentinela FATEC')]
class Usuarios extends Component
{
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

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $usuarios = [];

    public function mount(): void
    {
        // TODO [Backend]:
        // Substituir esta lista inicial pela consulta Eloquent ao banco de dados:
        // $this->usuarios = Usuario::latest()->get()->toArray();
        $this->usuarios = [
            [
                'id' => 1,
                'nome' => 'Carlos Eduardo Silva',
                'cpf' => '234.567.890-12',
                'email' => 'carlos.silva@fatec.sp.gov.br',
                'perfil' => 'admin',
                'perfil_label' => 'Administrador Geral',
                'codigo_operador' => 'ADM-001',
                'ativo' => true,
                'created_at' => '10/01/2026',
            ],
            [
                'id' => 2,
                'nome' => 'José Roberto de Souza (Guarda Silva)',
                'cpf' => '345.678.901-23',
                'email' => 'jose.roberto@fatec.sp.gov.br',
                'perfil' => 'operador',
                'perfil_label' => 'Operador de Portaria',
                'codigo_operador' => 'GDA-104',
                'ativo' => true,
                'created_at' => '15/01/2026',
            ],
            [
                'id' => 3,
                'nome' => 'Antônio Pereira Ribeiro (Guarda Ribeiro)',
                'cpf' => '456.789.012-34',
                'email' => 'antonio.ribeiro@fatec.sp.gov.br',
                'perfil' => 'operador',
                'perfil_label' => 'Operador de Portaria',
                'codigo_operador' => 'GDA-105',
                'ativo' => true,
                'created_at' => '20/01/2026',
            ],
            [
                'id' => 4,
                'nome' => 'Mariana Silveira Ramos',
                'cpf' => '567.890.123-45',
                'email' => 'mariana.silveira@fatec.sp.gov.br',
                'perfil' => 'supervisor',
                'perfil_label' => 'Supervisor de Segurança',
                'codigo_operador' => 'SUP-201',
                'ativo' => true,
                'created_at' => '02/02/2026',
            ],
            [
                'id' => 5,
                'nome' => 'Marcos Vinícius Costa',
                'cpf' => '678.901.234-56',
                'email' => 'marcos.costa@fatec.sp.gov.br',
                'perfil' => 'operador',
                'perfil_label' => 'Operador de Portaria',
                'codigo_operador' => 'GDA-106',
                'ativo' => false,
                'created_at' => '12/02/2026',
            ],
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $this->editingId = $id;

        foreach ($this->usuarios as $user) {
            if ($user['id'] === $id) {
                $this->nome = $user['nome'];
                $this->cpf = $user['cpf'];
                $this->email = $user['email'];
                $this->perfil = $user['perfil'];
                $this->codigo_operador = $user['codigo_operador'];
                $this->ativo = (bool) $user['ativo'];
                $this->showFormModal = true;

                return;
            }
        }
    }

    public function closeModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate([
            'nome' => 'required|min:3|max:100',
            'cpf' => 'required|min:11|max:14',
            'email' => 'required|email|max:100',
            'perfil' => 'required|in:admin,operador,supervisor',
            'codigo_operador' => 'required|min:3|max:20',
        ]);

        if ($this->editingId === null) {
            $this->validate([
                'senha' => 'required|min:6|confirmed',
            ]);
        } elseif (! empty($this->senha)) {
            $this->validate([
                'senha' => 'min:6|confirmed',
            ]);
        }

        $perfilLabels = [
            'admin' => 'Administrador Geral',
            'operador' => 'Operador de Portaria',
            'supervisor' => 'Supervisor de Segurança',
        ];

        // TODO [Backend]:
        // Se $this->editingId for null:
        //    Usuario::create([
        //        'nome' => $this->nome,
        //        'cpf' => $this->cpf,
        //        'email' => $this->email,
        //        'perfil' => $this->perfil,
        //        'codigo_operador' => strtoupper($this->codigo_operador),
        //        'senha' => Hash::make($this->senha),
        //        'ativo' => $this->ativo,
        //    ]);
        // Se $this->editingId existir:
        //    $user = Usuario::findOrFail($this->editingId);
        //    $data = ['nome', 'cpf', 'email', 'perfil', 'codigo_operador', 'ativo'];
        //    if (!empty($this->senha)) { $data['senha'] = Hash::make($this->senha); }
        //    $user->update($data);

        if ($this->editingId === null) {
            $newId = count($this->usuarios) + 1;
            $this->usuarios[] = [
                'id' => $newId,
                'nome' => $this->nome,
                'cpf' => $this->cpf,
                'email' => $this->email,
                'perfil' => $this->perfil,
                'perfil_label' => $perfilLabels[$this->perfil] ?? 'Operador',
                'codigo_operador' => strtoupper(trim($this->codigo_operador)),
                'ativo' => $this->ativo,
                'created_at' => date('d/m/Y'),
            ];

            $this->triggerToast('Novo usuário cadastrado com sucesso!', 'success');
        } else {
            foreach ($this->usuarios as &$user) {
                if ($user['id'] === $this->editingId) {
                    $user['nome'] = $this->nome;
                    $user['cpf'] = $this->cpf;
                    $user['email'] = $this->email;
                    $user['perfil'] = $this->perfil;
                    $user['perfil_label'] = $perfilLabels[$this->perfil] ?? 'Operador';
                    $user['codigo_operador'] = strtoupper(trim($this->codigo_operador));
                    $user['ativo'] = $this->ativo;
                    break;
                }
            }

            $this->triggerToast('Dados do usuário atualizados com sucesso!', 'success');
        }

        $this->closeModal();
    }

    public function toggleStatus(int $id): void
    {
        // TODO [Backend]:
        // $user = Usuario::findOrFail($id);
        // $user->update(['ativo' => !$user->ativo]);

        foreach ($this->usuarios as &$user) {
            if ($user['id'] === $id) {
                $user['ativo'] = ! $user['ativo'];
                $statusMsg = $user['ativo'] ? 'ativado' : 'desativado';
                $this->triggerToast("Usuário {$user['nome']} foi {$statusMsg}!", 'info');
                break;
            }
        }
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

    public function getFilteredUsuariosProperty(): array
    {
        return array_filter($this->usuarios, function ($user) {
            // Busca por texto
            if (! empty(trim($this->search))) {
                $term = trim($this->search);
                $matchName = stripos($user['nome'], $term) !== false;
                $matchEmail = stripos($user['email'], $term) !== false;
                $matchCpf = stripos($user['cpf'], $term) !== false;
                $matchCode = stripos($user['codigo_operador'], $term) !== false;

                if (! $matchName && ! $matchEmail && ! $matchCpf && ! $matchCode) {
                    return false;
                }
            }

            // Filtro por perfil
            if ($this->perfilFilter !== 'todos' && $user['perfil'] !== $this->perfilFilter) {
                return false;
            }

            // Filtro por status
            if ($this->statusFilter === 'ativo' && ! $user['ativo']) {
                return false;
            }
            if ($this->statusFilter === 'inativo' && $user['ativo']) {
                return false;
            }

            return true;
        });
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
            'filteredUsuarios' => $this->filteredUsuarios,
        ]);
    }
}
