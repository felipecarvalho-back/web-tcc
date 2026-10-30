<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Condutor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'condutores';

    protected $fillable = [
        'nome',
        'cpf',
        'tipo',
        'validade_acesso',
        'ativo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    /**
     * Especialização de Professor
     *
     * @return HasOne<CondutorProfessor, $this>
     */
    public function professor(): HasOne
    {
        return $this->hasOne(CondutorProfessor::class, 'condutor_id');
    }

    /**
     * Especialização de Funcionário
     *
     * @return HasOne<CondutorFuncionario, $this>
     */
    public function funcionario(): HasOne
    {
        return $this->hasOne(CondutorFuncionario::class, 'condutor_id');
    }

    /**
     * Especialização de Prestador de Serviço
     *
     * @return HasOne<CondutorPrestador, $this>
     */
    public function prestador(): HasOne
    {
        return $this->hasOne(CondutorPrestador::class, 'condutor_id');
    }

    /**
     * Especialização de Visitante
     *
     * @return HasOne<CondutorVisitante, $this>
     */
    public function visitante(): HasOne
    {
        return $this->hasOne(CondutorVisitante::class, 'condutor_id');
    }

    /**
     * Veículos vinculados ao condutor (1:N)
     *
     * @return HasMany<Veiculo, $this>
     */
    public function veiculos(): HasMany
    {
        return $this->hasMany(Veiculo::class, 'condutor_id');
    }

    /**
     * Registros de passagens na cancela
     *
     * @return HasMany<RegistroAcesso, $this>
     */
    public function registrosAcesso(): HasMany
    {
        return $this->hasMany(RegistroAcesso::class, 'condutor_id');
    }

    /**
     * Formata o código de acesso inserindo o hífen após os 3 primeiros caracteres (ex: DOC-88312).
     */
    public static function formatarCodigoAcesso(?string $codigo): string
    {
        $valor = strtoupper(trim((string) $codigo));
        if ($valor === '') {
            return '';
        }

        $limpo = (string) preg_replace('/[^A-Z0-9]/', '', $valor);
        if (strlen($limpo) > 3) {
            return substr($limpo, 0, 3).'-'.substr($limpo, 3, 5);
        }

        return $limpo;
    }

    /**
     * Retorna o código de acesso funcional do condutor de acordo com sua categoria.
     */
    public function codigoAcesso(): ?string
    {
        $codigo = match ($this->tipo) {
            'professor' => $this->professor?->codigo_acesso,
            'funcionario' => $this->funcionario?->codigo_acesso,
            'prestador' => $this->prestador?->codigo_acesso,
            default => null,
        };

        return $codigo ? self::formatarCodigoAcesso($codigo) : null;
    }

    /**
     * Busca um condutor ativo pelo código de acesso nas categorias permitidas.
     *
     * @param  array<int, string>  $tiposPermitidos
     */
    public static function buscarAtivoPorCodigo(
        string $codigo,
        array $tiposPermitidos = ['professor', 'funcionario', 'prestador']
    ): ?self {
        $codigoFormatado = self::formatarCodigoAcesso($codigo);
        if ($codigoFormatado === '') {
            return null;
        }

        $semHifen = str_replace('-', '', $codigoFormatado);
        $candidatos = array_values(array_unique([$codigoFormatado, $semHifen]));

        return self::query()
            ->with(['professor', 'funcionario', 'prestador', 'veiculos'])
            ->where('ativo', true)
            ->whereIn('tipo', $tiposPermitidos)
            ->where(function (Builder $q) use ($candidatos): void {
                $q->whereHas('professor', fn (Builder $pq) => $pq->whereIn('codigo_acesso', $candidatos))
                    ->orWhereHas('funcionario', fn (Builder $fq) => $fq->whereIn('codigo_acesso', $candidatos))
                    ->orWhereHas('prestador', fn (Builder $prq) => $prq->whereIn('codigo_acesso', $candidatos));
            })
            ->first();
    }

    /**
     * Retorna a data limite de validade de acesso como Carbon (se houver prazo determinado).
     */
    public function dataValidade(): ?Carbon
    {
        if (empty($this->validade_acesso) || strcasecmp($this->validade_acesso, 'Indeterminado') === 0) {
            return null;
        }

        try {
            $valor = trim($this->validade_acesso);
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $valor)) {
                return Carbon::parse($valor)->endOfDay();
            }
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}/', $valor)) {
                return Carbon::createFromFormat('d/m/Y', $valor)->endOfDay();
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    /**
     * Informa se o acesso do condutor está com o prazo expirado/vencido.
     */
    public function estaComAcessoExpirado(): bool
    {
        $validade = $this->dataValidade();
        if (! $validade) {
            return false;
        }

        return Carbon::now()->greaterThan($validade);
    }

    /**
     * Retorna array detalhado do status de validade para exibição no front-end.
     *
     * @return array{status: string, label: string, expirado: bool, dias_restantes: int|null, data_formatada: string}
     */
    public function statusValidade(): array
    {
        if (empty($this->validade_acesso) || strcasecmp($this->validade_acesso, 'Indeterminado') === 0) {
            return [
                'status' => 'permanente',
                'label' => 'Permanente',
                'expirado' => false,
                'dias_restantes' => null,
                'data_formatada' => 'Indeterminado',
            ];
        }

        $data = $this->dataValidade();
        if (! $data) {
            return [
                'status' => 'personalizado',
                'label' => $this->validade_acesso,
                'expirado' => false,
                'dias_restantes' => null,
                'data_formatada' => $this->validade_acesso,
            ];
        }

        $hoje = Carbon::today();
        $fimDia = $data->copy()->endOfDay();
        $expirado = Carbon::now()->greaterThan($fimDia);
        $diasRestantes = (int) $hoje->diffInDays($data, false);

        if ($expirado) {
            return [
                'status' => 'expirado',
                'label' => 'Expirado em '.$data->format('d/m/Y'),
                'expirado' => true,
                'dias_restantes' => $diasRestantes,
                'data_formatada' => $data->format('d/m/Y'),
            ];
        }

        if ($diasRestantes <= 7) {
            $avisoDias = $diasRestantes <= 0 ? 'Vence Hoje' : "Vence em {$diasRestantes}d";

            return [
                'status' => 'alerta',
                'label' => "{$avisoDias} ({$data->format('d/m/Y')})",
                'expirado' => false,
                'dias_restantes' => $diasRestantes,
                'data_formatada' => $data->format('d/m/Y'),
            ];
        }

        return [
            'status' => 'valido',
            'label' => 'Até '.$data->format('d/m/Y'),
            'expirado' => false,
            'dias_restantes' => $diasRestantes,
            'data_formatada' => $data->format('d/m/Y'),
        ];
    }

    /**
     * Formata a validade para exibição simples.
     */
    public function formatarValidadeParaExibicao(): string
    {
        return $this->statusValidade()['label'];
    }
}
