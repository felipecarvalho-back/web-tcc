<?php

namespace App\Models;

use App\Enums\StatusAcesso;
use App\Enums\TipoOcupante;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class RegistroAcesso extends Model
{
    use HasFactory, SoftDeletes;

    public const PLACA_NAO_IDENTIFICADA = 'SEM-PLACA';

    protected $table = 'registros_acesso';

    protected $fillable = [
        'veiculo_id',
        'condutor_id',
        'placa_registro',
        'cancela',
        'data_hora_entrada',
        'operador_id',
        'foto_entrada_path',
        'placa_corrigida',
        'data_hora_saida',
        'status',
        'justificativa',
        'tipo_ocupante',
        'passagem_origem_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_hora_entrada' => 'datetime',
            'data_hora_saida' => 'datetime',
            'status' => StatusAcesso::class,
            'tipo_ocupante' => TipoOcupante::class,
        ];
    }

    /**
     * Filtra os registros por período de data usando intervalos indexados (SARGable).
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeFiltrarData(Builder $query, string $periodo): Builder
    {
        return match ($periodo) {
            'hoje' => $query->whereBetween('data_hora_entrada', [
                Carbon::today()->startOfDay(),
                Carbon::today()->endOfDay(),
            ]),
            'ontem' => $query->whereBetween('data_hora_entrada', [
                Carbon::yesterday()->startOfDay(),
                Carbon::yesterday()->endOfDay(),
            ]),
            '7dias' => $query->where('data_hora_entrada', '>=', Carbon::today()->subDays(7)->startOfDay()),
            'mes' => $query->whereBetween('data_hora_entrada', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ]),
            default => $query,
        };
    }

    /**
     * Filtra os registros pela placa original ou corrigida (case-insensitive no PostgreSQL e MySQL).
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeFiltrarPlaca(Builder $query, string $busca): Builder
    {
        $placa = strtoupper(trim($busca));
        if ($placa === '') {
            return $query;
        }

        $semHifen = str_replace('-', '', $placa);

        return $query->where(function (Builder $q) use ($placa, $semHifen) {
            $q->whereLike('placa_registro', "%{$placa}%")
                ->orWhereLike('placa_corrigida', "%{$placa}%")
                ->orWhereLike('placa_registro', "%{$semHifen}%");
        });
    }

    /**
     * Filtra os registros pela categoria de vínculo do condutor.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeFiltrarCategoria(Builder $query, string $categoria): Builder
    {
        if ($categoria === '' || $categoria === 'todos') {
            return $query;
        }

        if ($categoria === 'visitante') {
            return $query->where(function (Builder $q) {
                $q->whereNull('condutor_id')
                    ->orWhereHas('condutor', fn (Builder $c) => $c->where('tipo', 'visitante'));
            });
        }

        return $query->whereHas('condutor', fn (Builder $c) => $c->where('tipo', $categoria));
    }

    /**
     * Filtra os registros pelo estado operacional da guarita.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeFiltrarStatus(Builder $query, string $filtroStatus): Builder
    {
        return match ($filtroStatus) {
            'pendente' => $query->where('status', StatusAcesso::Pendente)->whereNull('data_hora_saida'),
            'autorizado' => $query->whereIn('status', StatusAcesso::authorizedValues())->whereNull('data_hora_saida'),
            'nao_cadastrado' => $query->whereIn('status', StatusAcesso::unregisteredValues())->whereNull('data_hora_saida'),
            'bloqueado' => $query->where('status', StatusAcesso::Bloqueado)->whereNull('data_hora_saida'),
            'saida' => $query->where(function (Builder $q) {
                $q->where('status', StatusAcesso::Finalizado)->orWhereNotNull('data_hora_saida');
            }),
            default => $query,
        };
    }

    /**
     * Formata qualquer placa de 7 caracteres alfanuméricos no padrão AAA-0X00 com hífen.
     */
    public static function formatarPlaca(?string $placa): string
    {
        $valor = strtoupper(trim((string) $placa));
        if ($valor === '' || $valor === self::PLACA_NAO_IDENTIFICADA) {
            return $valor ?: self::PLACA_NAO_IDENTIFICADA;
        }

        $limpa = (string) preg_replace('/[^A-Z0-9]/', '', $valor);
        if (strlen($limpa) === 7) {
            return substr($limpa, 0, 3).'-'.substr($limpa, 3);
        }

        return substr($valor, 0, 8);
    }

    /**
     * Retorna a placa prioritária para exibição (corrigida manualmente ou lida na entrada).
     */
    public function placaExibida(): string
    {
        $placa = (string) ($this->placa_corrigida ?: $this->placa_registro);

        return self::formatarPlaca($placa);
    }

    /**
     * Indica se o registro possui uma placa válida identificada ou corrigida.
     */
    public function temPlacaIdentificada(): bool
    {
        $placa = $this->placaExibida();

        return $placa !== '' && $placa !== self::PLACA_NAO_IDENTIFICADA;
    }

    /**
     * Retorna o nome do condutor ou um rótulo descritivo caso ainda não esteja vinculado.
     */
    public function nomeCondutorExibicao(): string
    {
        if ($this->condutor?->nome) {
            return $this->condutor->nome;
        }

        return ! $this->temPlacaIdentificada()
            ? 'Placa Não Identificada'
            : 'Condutor Não Vinculado';
    }

    /**
     * Retorna o rótulo institucional do vínculo e setor/departamento do condutor.
     */
    public function descricaoVinculo(): string
    {
        $condutor = $this->condutor;
        $tipo = $condutor?->tipo ?? 'visitante';

        return match ($tipo) {
            'professor' => 'Docente • '.($condutor?->professor?->departamento ?: 'Curso FATEC'),
            'funcionario' => 'Servidor • '.($condutor?->funcionario?->setor ?: 'Administração'),
            'prestador' => 'Prestador • '.($condutor?->prestador?->empresa ?: 'Serviço Terceirizado'),
            'visitante' => 'Visitante • '.($condutor?->visitante?->motivo_visita ?: ($this->justificativa ?: 'Acesso Pontual')),
            default => 'Não Cadastrado',
        };
    }

    /**
     * Formata o registro para exibição na tabela de monitoramento em tempo real.
     *
     * @return array<string, mixed>
     */
    public function toMonitoringArray(): array
    {
        $statusEnum = $this->status instanceof StatusAcesso
            ? $this->status
            : StatusAcesso::tryFrom((string) $this->status) ?? StatusAcesso::Pendente;

        $placaExibida = $this->placaExibida();
        $temSaida = $this->data_hora_saida !== null;
        $temPlacaCorrigida = ! empty($this->placa_corrigida);
        $semPlaca = ! $this->temPlacaIdentificada();

        $detalhes = array_filter([
            $this->cancela,
            $this->operador ? 'Operador: '.$this->operador->nome : null,
            $this->justificativa,
        ]);

        return [
            'id' => $this->id,
            'plate' => $placaExibida,
            'raw_ocr' => $this->placa_registro,
            'driver_name' => $this->nomeCondutorExibicao(),
            'category' => $this->condutor?->tipo ?? 'visitante',
            'category_label' => implode(' • ', $detalhes),
            'registered_at' => $this->data_hora_entrada?->format('H:i:s') ?? '--:--:--',
            'time_ago' => $this->data_hora_entrada?->locale('pt_BR')->diffForHumans() ?? 'Agora mesmo',
            'status' => $statusEnum->viewState($temSaida),
            'status_label' => $statusEnum->label($temSaida, $temPlacaCorrigida, $semPlaca),
            'image_url' => $this->foto_entrada_path ?: 'https://placehold.co/300x200?text=Sem+Imagem',
            'is_registered' => (bool) $this->veiculo_id,
            'is_visitor' => $this->condutor?->tipo === 'visitante',
            'driver_validity' => $this->condutor?->formatarValidadeParaExibicao() ?? 'Permanente',
            'is_driver_expired' => $this->condutor?->estaComAcessoExpirado() ?? false,
        ];
    }

    /**
     * Veículo da passagem (se cadastrado)
     *
     * @return BelongsTo<Veiculo, $this>
     */
    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    /**
     * Condutor associado à passagem
     *
     * @return BelongsTo<Condutor, $this>
     */
    public function condutor(): BelongsTo
    {
        return $this->belongsTo(Condutor::class, 'condutor_id');
    }

    /**
     * Operador/Porteiro que registrou a passagem
     *
     * @return BelongsTo<Usuario, $this>
     */
    public function operador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'operador_id');
    }

    /**
     * Passagem principal do condutor (caso este registro seja de um carona/passageiro)
     *
     * @return BelongsTo<RegistroAcesso, $this>
     */
    public function passagemOrigem(): BelongsTo
    {
        return $this->belongsTo(RegistroAcesso::class, 'passagem_origem_id');
    }

    /**
     * Caronas/Passageiros que entraram junto nesta mesma passagem do veículo
     *
     * @return HasMany<RegistroAcesso, $this>
     */
    public function caronas(): HasMany
    {
        return $this->hasMany(RegistroAcesso::class, 'passagem_origem_id');
    }
}
