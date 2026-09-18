<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Acesso ao Sistema - Sentinela FATEC')]
class Login extends Component
{
    public string $accessCode = 'GDA-104';

    public string $password = '123456';

    public bool $remember = true;

    public function login(): mixed
    {
        if (str_starts_with(strtoupper(trim($this->accessCode)), 'ADM')) {
            return $this->redirectRoute('admin.dashboard', navigate: true);
        }

        return $this->redirectRoute('portaria.monitoramento', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
