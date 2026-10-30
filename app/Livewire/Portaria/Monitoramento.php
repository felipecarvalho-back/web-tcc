<?php

namespace App\Livewire\Portaria;

use App\Enums\StatusAcesso;
use App\Enums\TipoOcupante;
use App\Livewire\Concerns\HasToast;
use App\Models\Condutor;
use App\Models\RegistroAcesso;
use App\Models\Veiculo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property-read LengthAwarePaginator $filteredRecords
 */
#[Layout('layouts.app')]
#[Title('Monitoramento de Portaria - Sentinela FATEC')]
class Monitoramento extends Component
{
    use HasToast, WithPagination;

    public string $filterDate = 'hoje';

    public string $filterPlate = '';

    public string $filterCategory = 'todos';

    public string $filterStatus = 'todos';

    public function updatedFilterDate(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPlate(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    // Estados dos Modais Operacionais
    public bool $showVerificationModal = false;

    public bool $showDriverConfirmationModal = false;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $selectedRecord = null;

    /**
     * Modo do modal de verificação: 'corrigir_placa' (Todos) ou 'carro_terceiro' (Professor/Funcionário).
     */
    public string $verificationMode = 'corrigir_placa';

    // Campos da Dupla Verificação (Placa não identificada / Carro de Terceiro)
    public string $correctedPlate = '';

    public string $driverAccessCode = '';

    public string $justification = 'Correção manual de leitura OCR';

    // Campos da Confirmação de Condutor (Veículo identificado no banco)
    public string $confirmDriverCode = '';

    /**
     * Lista de códigos funcionais de caronas/passageiros (máx. 4 passageiros adicionais).
     *
     * @var array<int, string>
     */
    public array $passengers = [];

    public string $newPassengerCode = '';

    // Indicadores operacionais do topo da tela
    public int $totalPassages = 0;

    public int $automaticPassages = 0;

    public int $manualCorrections = 0;

    public int $pendingPassages = 0;

    public int $blockedPassages = 0;

    // Estado do Modal de Bloqueio de Acesso
    public bool $showBlockModal = false;

    public string $blockReason = 'Condutor não possui código de acesso';

    public string $blockCustomJustification = '';

    public function updatedVerificationMode(string $mode): void
    {
        $this->resetValidation();
        $this->justification = $mode === 'carro_terceiro'
            ? 'Veículo de terceiro / Carro emprestado de amigo ou parente'
            : 'Correção manual de leitura OCR';
    }

    /**
     * Atualiza os indicadores operacionais do topo em uma única query SQL otimizada.
     */
    private function loadMetrics(): void
    {
        $metricas = RegistroAcesso::query()
            ->where('tipo_ocupante', TipoOcupante::Condutor)
            ->filtrarData('hoje')
            ->selectRaw('
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN placa_registro != ? AND placa_corrigida IS NULL AND status IN (?, ?) THEN 1 ELSE 0 END), 0) as automaticas,
                COALESCE(SUM(CASE WHEN placa_corrigida IS NOT NULL THEN 1 ELSE 0 END), 0) as manuais,
                COALESCE(SUM(CASE WHEN status = ? AND data_hora_saida IS NULL THEN 1 ELSE 0 END), 0) as pendentes,
                COALESCE(SUM(CASE WHEN status = ? AND data_hora_saida IS NULL THEN 1 ELSE 0 END), 0) as bloqueados
            ', [
                RegistroAcesso::PLACA_NAO_IDENTIFICADA,
                StatusAcesso::Autorizado->value,
                StatusAcesso::EmPatio->value,
                StatusAcesso::Pendente->value,
                StatusAcesso::Bloqueado->value,
            ])
            ->first();

        $this->totalPassages = (int) ($metricas->total ?? 0);
        $this->automaticPassages = (int) ($metricas->automaticas ?? 0);
        $this->manualCorrections = (int) ($metricas->manuais ?? 0);
        $this->pendingPassages = (int) ($metricas->pendentes ?? 0);
        $this->blockedPassages = (int) ($metricas->bloqueados ?? 0);
    }

    /**
     * Consulta e formata os registros filtrados e paginados diretamente no banco de dados usando Query Scopes.
     */
    #[Computed]
    public function filteredRecords(): LengthAwarePaginator
    {
        $query = RegistroAcesso::query()
            ->select([
                'id',
                'veiculo_id',
                'condutor_id',
                'placa_registro',
                'placa_corrigida',
                'cancela',
                'data_hora_entrada',
                'data_hora_saida',
                'operador_id',
                'foto_entrada_path',
                'status',
                'justificativa',
            ])
            ->with([
                'operador:id,nome',
                'condutor:id,nome,tipo,validade_acesso',
                'condutor.professor:condutor_id,codigo_acesso,departamento',
                'condutor.funcionario:condutor_id,codigo_acesso,setor',
                'condutor.prestador:condutor_id,codigo_acesso,empresa',
            ])
            ->where('tipo_ocupante', TipoOcupante::Condutor)
            ->filtrarCategoria($this->filterCategory)
            ->filtrarData($this->filterDate)
            ->filtrarPlaca($this->filterPlate)
            ->filtrarStatus($this->filterStatus);

        $query->latest('data_hora_entrada')->latest('id');

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate(10);

        return $paginator->through(function ($registro): array {
            /** @var RegistroAcesso $registro */
            return $registro->toMonitoringArray();
        });
    }

    /**
     * Adiciona um código de carona/passageiro à lista de ocupantes (exclusivo para Docentes e Funcionários, até 4 caronas).
     */
    public function addPassenger(): void
    {
        $this->resetErrorBag('newPassengerCode');

        $code = strtoupper(trim($this->newPassengerCode));
        if ($code === '') {
            return;
        }

        if (count($this->passengers) >= 4) {
            $this->triggerToast('Capacidade máxima de 4 caronas atingida para este veículo.', 'warning');

            return;
        }

        $codigoMotorista = strtoupper(trim($this->confirmDriverCode ?: $this->driverAccessCode));
        if ($codigoMotorista !== '' && $code === $codigoMotorista) {
            $this->addError('newPassengerCode', 'O código informado já é o do motorista principal.');

            return;
        }

        if (in_array($code, $this->passengers, true)) {
            $this->addError('newPassengerCode', 'Este código de carona já foi adicionado.');

            return;
        }

        $passageiro = Condutor::buscarAtivoPorCodigo($code, ['professor', 'funcionario']);
        if (! $passageiro) {
            $this->addError('newPassengerCode', 'Código de carona inválido. Informe o código de um Professor ou Funcionário ativo.');

            return;
        }

        if ($passageiro->estaComAcessoExpirado()) {
            $this->addError(
                'newPassengerCode',
                'A autorização deste carona expirou em '.$passageiro->dataValidade()?->format('d/m/Y').'.'
            );

            return;
        }

        $this->passengers[] = $code;
        $this->newPassengerCode = '';
    }

    /**
     * Remove um carona/passageiro da lista de ocupantes.
     */
    public function removePassenger(int $index): void
    {
        if (isset($this->passengers[$index])) {
            unset($this->passengers[$index]);
            $this->passengers = array_values($this->passengers);
        }
    }

    /**
     * Abre o modal de dupla verificação para veículos com placa não identificada ou carro de terceiros.
     */
    public function openVerificationModal(int $id): void
    {
        $registro = RegistroAcesso::with(['operador:id,nome', 'condutor:id,nome,tipo'])->find($id);
        if (! $registro) {
            return;
        }

        $this->resetValidation();

        $record = $registro->toMonitoringArray();
        $this->selectedRecord = $record;
        $this->verificationMode = 'corrigir_placa';
        $this->correctedPlate = $record['plate'] === RegistroAcesso::PLACA_NAO_IDENTIFICADA ? '' : $record['plate'];
        $this->driverAccessCode = '';
        $this->justification = 'Correção manual de leitura OCR';
        $this->passengers = [];
        $this->newPassengerCode = '';
        $this->showVerificationModal = true;
    }

    /**
     * Fecha o modal de dupla verificação.
     */
    public function closeVerificationModal(): void
    {
        $this->resetValidation();
        $this->showVerificationModal = false;
        $this->selectedRecord = null;
        $this->verificationMode = 'corrigir_placa';
        $this->correctedPlate = '';
        $this->driverAccessCode = '';
        $this->passengers = [];
        $this->newPassengerCode = '';
    }

    /**
     * Processa a validação da dupla verificação (Caso 2: Corrigir Placa + Código / Caso 3: Carro de Terceiros) e libera a cancela.
     */
    public function confirmVerification(): void
    {
        if (! $this->selectedRecord) {
            return;
        }

        $this->resetValidation();

        $targetId = $this->selectedRecord['id'];
        $registro = RegistroAcesso::find($targetId);
        if (! $registro) {
            return;
        }

        $codigoFormatado = strtoupper(trim($this->driverAccessCode));
        $placaFormatada = trim($this->correctedPlate) !== ''
            ? RegistroAcesso::formatarPlaca($this->correctedPlate)
            : '';

        if ($this->verificationMode === 'carro_terceiro') {
            if ($codigoFormatado === '') {
                $this->addError('driverAccessCode', 'Informe o código de acesso do Professor ou Funcionário.');

                return;
            }

            $prestador = Condutor::buscarAtivoPorCodigo($codigoFormatado, ['prestador']);
            if ($prestador) {
                $this->addError(
                    'driverAccessCode',
                    'Acesso com carro de terceiros é permitido exclusivamente para Professores e Funcionários.'
                );

                return;
            }

            $condutor = Condutor::buscarAtivoPorCodigo($codigoFormatado, ['professor', 'funcionario']);
            if (! $condutor) {
                $this->addError(
                    'driverAccessCode',
                    'Código de acesso inválido ou inativo. Informe um código de Professor ou Funcionário.'
                );

                return;
            }

            if ($condutor->estaComAcessoExpirado()) {
                $this->addError(
                    'driverAccessCode',
                    'A autorização deste condutor expirou em '.$condutor->dataValidade()?->format('d/m/Y').'. Acesso bloqueado.'
                );

                return;
            }

            DB::transaction(function () use ($registro, $condutor, $placaFormatada, $codigoFormatado): void {
                if ($placaFormatada !== '' && $placaFormatada !== $registro->placa_registro) {
                    $registro->placa_corrigida = substr($placaFormatada, 0, 10);
                }

                $registro->condutor_id = $condutor->id;
                $registro->veiculo_id = null;
                $registro->operador_id = Auth::id() ?: $registro->operador_id;
                $registro->status = StatusAcesso::LiberadoManual;
                $registro->justificativa = "Carro de terceiro liberado por código ({$codigoFormatado})";
                $registro->save();

                $this->registrarCaronas($registro);
            });
        } else {
            // Modo 'corrigir_placa': Exige Placa Corrigida + Código de Acesso (Professor, Funcionário ou Prestador)
            if ($placaFormatada === '' || strlen(str_replace('-', '', $placaFormatada)) < 7) {
                $this->addError('correctedPlate', 'Informe a placa corrigida completa do veículo (7 caracteres).');
            }

            if ($codigoFormatado === '') {
                $this->addError('driverAccessCode', 'Informe o código de acesso do condutor para validar a placa.');
            }

            if ($this->getErrorBag()->isNotEmpty()) {
                return;
            }

            $condutor = Condutor::buscarAtivoPorCodigo($codigoFormatado, ['professor', 'funcionario', 'prestador']);
            if (! $condutor) {
                $this->addError('driverAccessCode', 'Código de acesso inválido ou condutor inativo no sistema.');

                return;
            }

            if ($condutor->estaComAcessoExpirado()) {
                $this->addError(
                    'driverAccessCode',
                    'A autorização deste condutor expirou em '.$condutor->dataValidade()?->format('d/m/Y').'. Acesso bloqueado.'
                );

                return;
            }

            $veiculo = Veiculo::buscarAtivoPorPlaca($placaFormatada);
            if (! $veiculo) {
                $this->addError(
                    'correctedPlate',
                    'Esta placa não está cadastrada no banco. Caso seja veículo emprestado, selecione a opção "Carro de Terceiro".'
                );

                return;
            }

            if ($veiculo->condutor_id !== $condutor->id) {
                $this->addError(
                    'driverAccessCode',
                    'O código de acesso informado não pertence ao proprietário cadastrado desta placa.'
                );

                return;
            }

            DB::transaction(function () use ($registro, $condutor, $veiculo, $placaFormatada, $codigoFormatado): void {
                if ($placaFormatada !== $registro->placa_registro) {
                    $registro->placa_corrigida = substr($placaFormatada, 0, 10);
                }

                $registro->veiculo_id = $veiculo->id;
                $registro->condutor_id = $condutor->id;
                $registro->operador_id = Auth::id() ?: $registro->operador_id;
                $registro->status = StatusAcesso::LiberadoManual;
                $registro->justificativa = "Placa corrigida ({$placaFormatada}) e código validado ({$codigoFormatado})";
                $registro->save();

                if (in_array($condutor->tipo, ['professor', 'funcionario'], true)) {
                    $this->registrarCaronas($registro);
                }
            });
        }

        $passengerCount = count($this->passengers);
        $passengerNote = $passengerCount > 0 ? " (+{$passengerCount} carona(s) registrada(s))" : '';

        $this->closeVerificationModal();
        unset($this->filteredRecords);
        $this->triggerToast("Acesso validado e cancela liberada{$passengerNote}!", 'success');
    }

    /**
     * Abre o modal de confirmação de condutor para veículos cuja placa já foi identificada no banco.
     */
    public function openDriverConfirmationModal(int $id): void
    {
        $registro = RegistroAcesso::with([
            'operador:id,nome',
            'condutor:id,nome,tipo,validade_acesso',
            'condutor.professor:condutor_id,codigo_acesso,departamento',
            'condutor.funcionario:condutor_id,codigo_acesso,setor',
            'condutor.prestador:condutor_id,codigo_acesso,empresa',
        ])->find($id);

        if (! $registro) {
            return;
        }

        $this->resetValidation();

        // Caso o registro tenha placa mas ainda não tenha vínculo salvo de veiculo_id/condutor_id, vincula automaticamente
        if (! $registro->condutor_id && $registro->temPlacaIdentificada()) {
            $veiculo = Veiculo::buscarAtivoPorPlaca($registro->placaExibida());
            if ($veiculo) {
                $registro->veiculo_id = $veiculo->id;
                $registro->condutor_id = $veiculo->condutor_id;
                $registro->save();
                $registro->setRelation('condutor', $veiculo->condutor);
            }
        }

        $this->selectedRecord = $registro->toMonitoringArray();

        $condutorVinculado = $registro->condutor ?: $veiculo?->condutor ?? null;
        if ($condutorVinculado) {
            $this->selectedRecord['driver_validity'] = $condutorVinculado->formatarValidadeParaExibicao();
            $this->selectedRecord['is_driver_expired'] = $condutorVinculado->estaComAcessoExpirado();
        }

        $this->confirmDriverCode = '';
        $this->passengers = [];
        $this->newPassengerCode = '';
        $this->showDriverConfirmationModal = true;
    }

    /**
     * Fecha o modal de confirmação de condutor.
     */
    public function closeDriverConfirmationModal(): void
    {
        $this->resetValidation();
        $this->showDriverConfirmationModal = false;
        $this->selectedRecord = null;
        $this->confirmDriverCode = '';
        $this->passengers = [];
        $this->newPassengerCode = '';
    }

    /**
     * Caso 1: Placa identificada no banco -> valida apenas o código de acesso (Professor, Funcionário ou Prestador) e libera o carro.
     */
    public function confirmDriver(): void
    {
        if (! $this->selectedRecord) {
            return;
        }

        $this->resetValidation();

        $codigoFormatado = strtoupper(trim($this->confirmDriverCode));
        if ($codigoFormatado === '') {
            $this->addError('confirmDriverCode', 'Informe o código de acesso do condutor para liberar a cancela.');

            return;
        }

        $targetId = $this->selectedRecord['id'];
        $registro = RegistroAcesso::with(['condutor', 'veiculo'])->find($targetId);
        if (! $registro) {
            return;
        }

        $condutor = Condutor::buscarAtivoPorCodigo($codigoFormatado, ['professor', 'funcionario', 'prestador']);
        if (! $condutor) {
            $this->addError('confirmDriverCode', 'Código de acesso inválido ou condutor inativo no sistema.');

            return;
        }

        if ($condutor->estaComAcessoExpirado()) {
            $this->addError(
                'confirmDriverCode',
                'A autorização deste condutor expirou em '.$condutor->dataValidade()?->format('d/m/Y').'. Acesso bloqueado pela guarita.'
            );

            return;
        }

        $veiculo = $registro->veiculo ?: Veiculo::buscarAtivoPorPlaca($registro->placaExibida());

        if ($registro->condutor_id && $condutor->id !== $registro->condutor_id) {
            $this->addError('confirmDriverCode', 'O código informado não corresponde ao condutor titular deste veículo.');

            return;
        }

        if ($veiculo && $veiculo->condutor_id !== $condutor->id) {
            $this->addError('confirmDriverCode', 'O código informado não pertence ao proprietário cadastrado desta placa.');

            return;
        }

        DB::transaction(function () use ($registro, $condutor, $veiculo, $codigoFormatado): void {
            $registro->condutor_id = $condutor->id;
            $registro->veiculo_id = $veiculo?->id ?? $registro->veiculo_id;
            $registro->operador_id = Auth::id() ?: $registro->operador_id;
            $registro->status = StatusAcesso::Autorizado;
            $registro->justificativa = "Condutor confirmado por código ({$codigoFormatado})";
            $registro->save();

            if (in_array($condutor->tipo, ['professor', 'funcionario'], true)) {
                $this->registrarCaronas($registro);
            }
        });

        $passengerCount = count($this->passengers);
        $passengerNote = $passengerCount > 0 ? " (+{$passengerCount} carona(s) registrada(s))" : '';

        $this->closeDriverConfirmationModal();
        unset($this->filteredRecords);
        $this->triggerToast("Identidade do condutor confirmada{$passengerNote}! Cancela liberada.", 'success');
    }

    /**
     * Registra os passageiros/caronas vinculados à passagem principal do veículo.
     */
    private function registrarCaronas(RegistroAcesso $passagemPrincipal): void
    {
        $passagemPrincipal->loadMissing('condutor');

        foreach ($this->passengers as $codigoCarona) {
            $passageiro = Condutor::buscarAtivoPorCodigo($codigoCarona, ['professor', 'funcionario']);
            if (! $passageiro) {
                continue;
            }

            RegistroAcesso::create([
                'veiculo_id' => $passagemPrincipal->veiculo_id,
                'condutor_id' => $passageiro->id,
                'tipo_ocupante' => TipoOcupante::Passageiro,
                'passagem_origem_id' => $passagemPrincipal->id,
                'placa_registro' => $passagemPrincipal->placa_registro,
                'placa_corrigida' => $passagemPrincipal->placa_corrigida,
                'cancela' => $passagemPrincipal->cancela,
                'data_hora_entrada' => $passagemPrincipal->data_hora_entrada ?? Carbon::now(),
                'operador_id' => Auth::id() ?: $passagemPrincipal->operador_id,
                'foto_entrada_path' => $passagemPrincipal->foto_entrada_path,
                'status' => $passagemPrincipal->status,
                'justificativa' => "Carona com {$passagemPrincipal->nomeCondutorExibicao()} ({$codigoCarona})",
            ]);
        }
    }

    /**
     * Registra a saída de um veículo no sistema (e das eventuais caronas vinculadas).
     */
    public function markExit(int $id): void
    {
        $registro = RegistroAcesso::find($id);

        if ($registro) {
            $agora = Carbon::now();

            DB::transaction(function () use ($registro, $agora): void {
                $registro->data_hora_saida = $agora;
                $registro->status = StatusAcesso::Finalizado;
                $registro->save();

                $registro->caronas()
                    ->whereNull('data_hora_saida')
                    ->update([
                        'data_hora_saida' => $agora,
                        'status' => StatusAcesso::Finalizado,
                    ]);
            });

            $placa = $registro->placaExibida();
            unset($this->filteredRecords);
            $this->triggerToast("Saída registrada para o veículo {$placa}!", 'info');
        }
    }

    /**
     * Abre o modal de bloqueio de acesso para veículos não cadastrados / sem código.
     */
    public function openBlockModal(int $id): void
    {
        $registro = RegistroAcesso::with(['operador:id,nome', 'condutor:id,nome,tipo,validade_acesso'])->find($id);
        if (! $registro) {
            return;
        }

        $this->resetValidation();
        $this->selectedRecord = $registro->toMonitoringArray();
        $this->showVerificationModal = false;
        $this->showDriverConfirmationModal = false;
        $this->blockReason = 'Condutor não possui código de acesso';
        $this->blockCustomJustification = '';
        $this->showBlockModal = true;
    }

    /**
     * Fecha o modal de bloqueio de acesso.
     */
    public function closeBlockModal(): void
    {
        $this->resetValidation();
        $this->showBlockModal = false;
        $this->selectedRecord = null;
        $this->blockReason = 'Condutor não possui código de acesso';
        $this->blockCustomJustification = '';
    }

    /**
     * Confirma o bloqueio do veículo na guarita e atualiza o status para Bloqueado.
     */
    public function confirmBlock(): void
    {
        if (! $this->selectedRecord) {
            return;
        }

        $targetId = $this->selectedRecord['id'];
        $registro = RegistroAcesso::find($targetId);
        if (! $registro) {
            return;
        }

        $motivo = trim($this->blockCustomJustification) !== ''
            ? trim($this->blockCustomJustification)
            : $this->blockReason;

        DB::transaction(function () use ($registro, $motivo): void {
            $registro->status = StatusAcesso::Bloqueado;
            $registro->justificativa = "Acesso bloqueado na guarita: {$motivo}";
            $registro->operador_id = Auth::id() ?: $registro->operador_id;
            $registro->save();
        });

        $this->closeBlockModal();
        unset($this->filteredRecords);
        $this->triggerToast('Acesso bloqueado com sucesso na guarita.', 'warning');
    }

    /**
     * Reverte o bloqueio de um registro para permitir reavaliação pela guarita.
     */
    public function unblockRecord(int $id): void
    {
        $registro = RegistroAcesso::find($id);
        if (! $registro) {
            return;
        }

        DB::transaction(function () use ($registro): void {
            $registro->status = StatusAcesso::NaoCadastrado;
            $registro->justificativa = 'Bloqueio revertido pelo operador para reavaliação';
            $registro->operador_id = Auth::id() ?: $registro->operador_id;
            $registro->save();
        });

        unset($this->filteredRecords);
        $this->triggerToast('Registro reaberto para avaliação da guarita.', 'info');
    }

    public function render()
    {
        $this->loadMetrics();

        return view('livewire.portaria.monitoramento', [
            'filteredRecords' => $this->filteredRecords,
        ]);
    }
}
