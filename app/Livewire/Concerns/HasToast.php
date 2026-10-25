<?php

namespace App\Livewire\Concerns;

trait HasToast
{
    public string $toastMessage = '';

    public string $toastType = 'success';

    public function triggerToast(string $message, string $type = 'success'): void
    {
        $this->toastMessage = $message;
        $this->toastType = $type;
    }

    public function clearToast(): void
    {
        $this->toastMessage = '';
    }
}
