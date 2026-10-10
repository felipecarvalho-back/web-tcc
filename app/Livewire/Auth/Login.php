<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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

        $throttleKey = Str::transliterate(Str::lower($this->accessCode).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('accessCode', "Muitas tentativas de login. Tente novamente em {$seconds} segundos.");

            return;
        }

        $credenciais = [
            'codigo_operador' => strtoupper(trim($this->accessCode)),
            'password' => $this->password,
            'ativo' => true,
        ];

        if (! Auth::attempt($credenciais, $this->remember)) {
            RateLimiter::hit($throttleKey, 60);
            $this->addError('accessCode', 'Código de acesso ou senha inválidos.');

            return;
        }

        RateLimiter::clear($throttleKey);

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
