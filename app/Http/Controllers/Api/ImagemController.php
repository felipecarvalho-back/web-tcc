<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class ImagemController extends Controller
{
    public function __construct(
        protected CloudinaryService $cloudinaryService
    ) {}

    public function enviar(Request $request): JsonResponse
    {
        // 1. Validação restrita apenas a PNG e JPG/JPEG (até 10MB para fotos de smartphones)
        $request->validate([
            'foto' => [
                'required',
                File::types(['png', 'jpg', 'jpeg'])->max(10240),
            ],
        ], [
            'foto.required' => 'O envio da foto é obrigatório.',
            'foto.mimes' => 'A imagem deve ser exclusivamente no formato PNG ou JPG.',
            'foto.max' => 'A imagem não pode ultrapassar o tamanho máximo de 10MB.',
        ]);

        $arquivo = $request->file('foto');

        // 2. Descobrir a extensão segura do arquivo (retorna 'png' ou 'jpg')
        $extensao = $arquivo->extension();

        // 3. Montar nome único seguro para evitar sobrescrita simultânea
        $nomeArquivo = 'placa-'.time().'-'.Str::random(8).'.'.$extensao;

        // 4. Se as variáveis de ambiente do Cloudinary estiverem configuradas, envia para a nuvem
        if ($this->cloudinaryService->isConfigured()) {
            $urlNuvem = $this->cloudinaryService->upload(
                $arquivo->getRealPath(),
                $nomeArquivo,
                'sentinela/placas'
            );

            if ($urlNuvem) {
                return response()->json([
                    'mensagem' => 'Imagem enviada com sucesso para a nuvem!',
                    'nome' => $nomeArquivo,
                    'caminho' => 'sentinela/placas/'.$nomeArquivo,
                    'url' => $urlNuvem,
                    'armazenamento' => 'cloudinary',
                ], 201);
            }
        }

        // 5. Caso não tenha as variáveis (ou falhe a nuvem), salva localmente no storage público
        $caminho = $arquivo->storeAs('perfis', $nomeArquivo, 'public');
        $urlFinal = Storage::url($caminho);

        return response()->json([
            'mensagem' => 'Imagem salva com sucesso localmente!',
            'nome' => $nomeArquivo,
            'caminho' => $caminho,
            'url' => $urlFinal,
            'armazenamento' => 'local',
        ], 201);
    }
}
