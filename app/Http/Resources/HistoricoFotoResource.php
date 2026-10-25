<?php

namespace App\Http\Resources;

use App\Models\Condutor;
use App\Models\RegistroAcesso;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RegistroAcesso
 */
class HistoricoFotoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $placaAtual = $this->placa_corrigida ?: $this->placa_registro;
        $placaIdentificada = $placaAtual !== '' && $placaAtual !== RegistroAcesso::PLACA_NAO_IDENTIFICADA;

        /** @var Condutor|null $condutor */
        $condutor = $this->condutor ?? $this->veiculo?->condutor;

        return [
            'id' => $this->id,
            'foto' => $this->foto_entrada_path,
            'horario' => $this->data_hora_entrada?->format('H:i:s'),
            'data_hora' => $this->data_hora_entrada?->format('d/m/Y H:i:s'),
            'placa' => $placaIdentificada ? $placaAtual : null,
            'placa_exibicao' => $placaAtual,
            'placa_identificada' => $placaIdentificada,
            'condutor' => $condutor?->nome ?? 'Condutor não identificado',
            'condutor_id' => $condutor?->id,
            'condutor_tipo' => $condutor?->tipo,
            'status' => $this->status?->value ?? $this->status,
        ];
    }
}
