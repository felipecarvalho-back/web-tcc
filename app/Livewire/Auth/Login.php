<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

#[Layout('layouts.guest')]
#[Title('Acesso ao Sistema - Sentinela FATEC')]
class Login extends Component
{
    #[Validate('required', message: 'Informe o código de acesso.')]
    public string $accessCode = '';

    #[Validate('required', message: 'Informe a senha.')]
    public string $password = '';

    public function login()
    {
        $this->validate();

        // Cria uma chave única com o código e o IP
        $throttleKey = Str::transliterate(Str::lower($this->accessCode) . '|' . request()->ip());

        // Se errou mais de 5 vezes:
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            // Pega quantos segundos faltam para liberar
            $seconds = RateLimiter::availableIn($throttleKey);
            // Exibe o erro na tela
            $this->addError('accessCode', "Muitas tentativas de login. Tente novamente em {$seconds} segundos.");

            return;
        }

        try {
            $credenciais = [
                'codigo_operador' => strtoupper(trim($this->accessCode)),
                'password' => $this->password,
                'ativo' => true,
            ];

            if (! Auth::attempt($credenciais)) {
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
        } catch (Throwable $e) {
            Log::error('Falha interna ao processar login: ' . $e->getMessage(), [
                'accessCode' => $this->accessCode,
                'exception' => $e,
            ]);

            $this->addError('accessCode', 'Serviço temporariamente indisponível. Em alguns instantes tente novamente.');
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
