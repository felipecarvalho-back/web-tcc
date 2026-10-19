<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatusAcesso;
use App\Enums\TipoOcupante;
use App\Http\Controllers\Controller;
use App\Models\RegistroAcesso;
use App\Services\CloudinaryService;
use App\Services\PlacaOcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class ImagemController extends Controller
{
    public function __construct(
        protected CloudinaryService $cloudinaryService,
        protected PlacaOcrService $placaOcrService
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
        $extensao = $arquivo->extension();
        $nomeArquivo = 'placa-'.time().'-'.Str::random(8).'.'.$extensao;
        $caminhoTemporario = $arquivo->getRealPath();

        // 2. Envia a imagem para a API em Python para detecção da placa via OCR
        $placaDetectada = $this->placaOcrService->detectarPlaca($caminhoTemporario, $nomeArquivo);
        $placaFormatada = $this->placaOcrService->formatarPlaca($placaDetectada);

        // 3. Armazena a imagem (na nuvem se configurado, ou localmente como fallback)
        $urlFinal = null;
        $caminho = null;
        $armazenamento = 'local';

        if ($this->cloudinaryService->isConfigured()) {
            $urlNuvem = $this->cloudinaryService->upload(
                $caminhoTemporario,
                $nomeArquivo,
                'sentinela/placas'
            );

            if ($urlNuvem) {
                $urlFinal = $urlNuvem;
                $caminho = 'sentinela/placas/'.$nomeArquivo;
                $armazenamento = 'cloudinary';
            }
        }

        if (! $urlFinal) {
            $caminho = $arquivo->storeAs('perfis', $nomeArquivo, 'public');
            $urlFinal = Storage::url($caminho);
            $armazenamento = 'local';
        }

        // 4. Registra a passagem na tabela registros_acesso usando os Enums tipados
        $registro = RegistroAcesso::create([
            'veiculo_id' => null,
            'condutor_id' => null,
            'tipo_ocupante' => TipoOcupante::Condutor,
            'placa_registro' => $placaFormatada,
            'cancela' => $request->input('cancela', 'Cancela 01 (Principal)'),
            'data_hora_entrada' => Carbon::now(),
            'operador_id' => $request->user()?->id,
            'foto_entrada_path' => $urlFinal,
            'placa_corrigida' => null,
            'status' => $placaDetectada ? StatusAcesso::Pendente : StatusAcesso::NaoCadastrado,
            'justificativa' => $placaDetectada
                ? 'Placa identificada via OCR'
                : 'Placa não identificada automaticamente pelo OCR',
        ]);

        return response()->json([
            'mensagem' => $armazenamento === 'cloudinary'
                ? 'Imagem enviada com sucesso para a nuvem!'
                : 'Imagem salva com sucesso localmente!',
            'nome' => $nomeArquivo,
            'caminho' => $caminho,
            'url' => $urlFinal,
            'armazenamento' => $armazenamento,
            'placa' => $placaFormatada,
            'placa_identificada' => $placaDetectada !== null,
            'registro_id' => $registro->id,
        ], 201);
    }
}
