<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    /**
     * Create a new class instance.
     */
    public function isConfigured(): bool
    {
        $cloudName = config('services.cloudinary.cloud_name') ?? env('CLOUDINARY_CLOUD_NAME');
        $apiKey = config('services.cloudinary.api_key') ?? env('CLOUDINARY_API_KEY');
        $apiSecret = config('services.cloudinary.api_secret') ?? env('CLOUDINARY_API_SECRET');

        return ! empty($cloudName) && ! empty($apiKey) && ! empty($apiSecret);
    }

    /**
     * Faz upload de uma imagem diretamente para o Cloudinary via API REST.
     * Retorna a URL pública HTTPS ou null se não configurado / erro.
     */
    public function upload(string $caminhoArquivo, ?string $nomeArquivo = null, string $pasta = 'sentinela/placas'): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $cloudName = config('services.cloudinary.cloud_name') ?? env('CLOUDINARY_CLOUD_NAME');
        $apiKey = config('services.cloudinary.api_key') ?? env('CLOUDINARY_API_KEY');
        $apiSecret = config('services.cloudinary.api_secret') ?? env('CLOUDINARY_API_SECRET');

        try {
            $timestamp = time();
            $params = [
                'folder' => $pasta,
                'timestamp' => $timestamp,
            ];

            ksort($params);

            $signatureString = http_build_query($params, '', '&').$apiSecret;
            $signature = sha1($signatureString);

            $filename = $nomeArquivo ?? basename($caminhoArquivo);

            $response = Http::timeout(30)
                ->attach('file', file_get_contents($caminhoArquivo), $filename)
                ->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
                    'api_key' => $apiKey,
                    'timestamp' => $timestamp,
                    'folder' => $pasta,
                    'signature' => $signature,
                ]);

            if ($response->successful()) {
                return $response->json('secure_url');
            }

            Log::error('Erro ao enviar imagem ao Cloudinary:', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('Exceção ao conectar com Cloudinary: '.$e->getMessage());

            return null;
        }
    }
}
