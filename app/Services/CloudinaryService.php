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
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

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

        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        try {
            $timestamp = time();
            $params = [
                'folder' => $pasta,
                'timestamp' => $timestamp,
            ];

            ksort($params);

            $signatureParts = [];
            foreach ($params as $paramKey => $paramValue) {
                $signatureParts[] = "{$paramKey}={$paramValue}";
            }
            $signatureString = implode('&', $signatureParts).$apiSecret;
            $signature = sha1($signatureString);

            $filename = $nomeArquivo ?? basename($caminhoArquivo);
            $stream = fopen($caminhoArquivo, 'r');
            if ($stream === false) {
                return null;
            }

            $connectTimeout = max(5, (int) config('services.cloudinary.connect_timeout', 15));
            $timeout = max($connectTimeout, (int) config('services.cloudinary.timeout', 60));

            try {
                $response = Http::connectTimeout($connectTimeout)
                    ->timeout($timeout)
                    ->attach('file', $stream, $filename)
                    ->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
                        'api_key' => $apiKey,
                        'timestamp' => $timestamp,
                        'folder' => $pasta,
                        'signature' => $signature,
                    ]);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

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
