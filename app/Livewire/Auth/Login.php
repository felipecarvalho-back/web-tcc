<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Acesso ao Sistema - Sentinela FATEC')]
class Login extends Component
{
    #[Validate('required', message: 'Informe o código de acesso.')]
    public string $accessCode = '';

    #[Validate('required', message: 'Informe a senha.')]
    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $this->validate();

        $credenciais = [
            'codigo_operador' => strtoupper(trim($this->accessCode)),
            'password' => $this->password, // o Laravel compara com a coluna "senha"
            'ativo' => true,            // usuário desativado não entra
        ];

        if (! Auth::attempt($credenciais, $this->remember)) {
            $this->addError('accessCode', 'Código de acesso ou senha inválidos.');

            return;
        }

        session()->regenerate(); // pulseira nova, por segurança

        $perfil = Auth::user()->perfil;

        return $perfil === 'admin'
            ? $this->redirectRoute('admin.dashboard', navigate: true)
            : $this->redirectRoute('portaria.monitoramento', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
