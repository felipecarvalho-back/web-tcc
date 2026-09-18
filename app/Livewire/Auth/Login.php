<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Login do Usuário - Sentinela FATEC')]
class Login extends Component
{
    public string $identifier = 'operador.silva@fatec.sp.gov.br';

    public string $password = 'fatec@2026';

    public string $workstation = 'principal';

    public bool $remember = true;

    public bool $isLoading = false;

    public function login(): mixed
    {
        $this->isLoading = true;

        if ($this->workstation === 'central_admin') {
            return $this->redirectRoute('admin.dashboard', navigate: true);
        }

        return $this->redirectRoute('portaria.monitoramento', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
