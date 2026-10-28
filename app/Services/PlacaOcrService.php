<?php

namespace App\Services;

use App\Models\RegistroAcesso;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlacaOcrService
{
    /**
     * Envia a imagem para a API Python (/detectar-placa) e retorna a placa identificada ou null.
     */
    public function detectarPlaca(string $caminhoArquivo, ?string $nomeArquivo = null): ?string
    {
        $baseUrl = rtrim((string) config('services.python_api.url'), '/');
        $token = config('services.python_api.token');

        if (empty($baseUrl)) {
            return null;
        }

        try {
            $filename = $nomeArquivo ?? basename($caminhoArquivo);

            $request = Http::timeout(120);

            if (! empty($token)) {
                $request = $request->withToken($token);
            }

            $response = $request
                ->attach('file', file_get_contents($caminhoArquivo), $filename)
                ->post("{$baseUrl}/detectar-placa");

            if ($response->successful()) {
                $dados = $response->json();

                $encontrada = (bool) ($dados['encontrada'] ?? false);
                $placa = $dados['placa'] ?? null;

                if ($encontrada && is_string($placa) && trim($placa) !== '') {
                    return strtoupper(trim($placa));
                }

                Log::info('API Python processou a imagem com sucesso, mas nenhuma placa foi identificada:', [
                    'mensagem' => $dados['mensagem'] ?? 'Nenhuma placa detectada',
                ]);

                return null;
            }

            Log::warning('API Python retornou erro HTTP na detecção de placa:', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('Erro ao conectar com a API Python de OCR: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Formata a placa para exibição/armazenamento (até 10 caracteres).
     */
    public function formatarPlaca(?string $placa): string
    {
        return RegistroAcesso::formatarPlaca($placa);
    }
}
