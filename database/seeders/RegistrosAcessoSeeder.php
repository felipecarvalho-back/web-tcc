<?php

namespace Database\Seeders;

use App\Enums\StatusAcesso;
use App\Enums\TipoOcupante;
use App\Models\Condutor;
use App\Models\RegistroAcesso;
use App\Models\Usuario;
use App\Models\Veiculo;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RegistrosAcessoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guardaSilva = Usuario::where('codigo_operador', 'GDA-104')->first();
        $guardaRibeiro = Usuario::where('codigo_operador', 'GDA-105')->first();

        $marcos = Condutor::where('cpf', '111.222.333-01')->first();
        $juliana = Condutor::where('cpf', '111.222.333-02')->first();
        $andre = Condutor::where('cpf', '111.222.333-03')->first();
        $carlos = Condutor::where('cpf', '111.222.333-04')->first();
        $mariana = Condutor::where('cpf', '111.222.333-05')->first();

        $veiculoMarcos = Veiculo::where('placa', 'BRA-2819')->first();
        $veiculoJuliana = Veiculo::where('placa', 'GTR-4C88')->first();
        $veiculoAndre = Veiculo::where('placa', 'KPL-3390')->first();
        $veiculoCarlos = Veiculo::where('placa', 'ABC-1234')->first();
        $veiculoMariana = Veiculo::where('placa', 'FTC-8821')->first();

        $registros = [
            // Segunda-feira (07/09/2026) - Prof. Dr. Marcos Souza
            [
                'id' => 10492,
                'veiculo_id' => $veiculoMarcos?->id,
                'condutor_id' => $marcos?->id,
                'placa_registro' => 'BRA-2819',
                'cancela' => 'Cancela 03 (Saída)',
                'data_hora_entrada' => Carbon::parse('2026-09-07 18:50:15'),
                'data_hora_saida' => Carbon::parse('2026-09-07 22:38:40'),
                'status' => StatusAcesso::Finalizado,
                'operador_id' => $guardaSilva?->id,
                'justificativa' => null,
                'tipo_ocupante' => TipoOcupante::Condutor,
            ],
            [
                'id' => 10491,
                'veiculo_id' => $veiculoMarcos?->id,
                'condutor_id' => $marcos?->id,
                'placa_registro' => 'BRA-2819',
                'cancela' => 'Cancela 01 (Principal)',
                'data_hora_entrada' => Carbon::parse('2026-09-07 18:50:15'),
                'data_hora_saida' => null,
                'status' => StatusAcesso::Autorizado,
                'operador_id' => $guardaSilva?->id,
                'justificativa' => null,
                'tipo_ocupante' => TipoOcupante::Condutor,
            ],
            // Segunda-feira (14/09/2026) - Prof. Dr. Marcos Souza
            [
                'id' => 10498,
                'veiculo_id' => $veiculoMarcos?->id,
                'condutor_id' => $marcos?->id,
                'placa_registro' => 'BRA-2819',
                'cancela' => 'Cancela 01 (Principal)',
                'data_hora_entrada' => Carbon::parse('2026-09-14 18:48:30'),
                'data_hora_saida' => null,
                'status' => StatusAcesso::Autorizado,
                'operador_id' => null,
                'justificativa' => null,
                'tipo_ocupante' => TipoOcupante::Condutor,
            ],
            [
                'id' => 10499,
                'veiculo_id' => $veiculoMarcos?->id,
                'condutor_id' => $marcos?->id,
                'placa_registro' => 'BRA-2819',
                'cancela' => 'Cancela 03 (Saída)',
                'data_hora_entrada' => Carbon::parse('2026-09-14 18:48:30'),
                'data_hora_saida' => Carbon::parse('2026-09-14 22:40:00'),
                'status' => StatusAcesso::Finalizado,
                'operador_id' => $guardaRibeiro?->id,
                'justificativa' => null,
                'tipo_ocupante' => TipoOcupante::Condutor,
            ],
            // Segunda-feira (21/09/2026) - Prof. Dr. Marcos Souza
            [
                'id' => 10503,
                'veiculo_id' => $veiculoMarcos?->id,
                'condutor_id' => $marcos?->id,
                'placa_registro' => 'BRA-2819',
                'cancela' => 'Cancela 01 (Principal)',
                'data_hora_entrada' => Carbon::parse('2026-09-21 18:42:05'),
                'data_hora_saida' => null,
                'status' => StatusAcesso::LiberadoManual,
                'operador_id' => $guardaSilva?->id,
                'justificativa' => 'Entrada c/ Validação de Código Funcional',
                'tipo_ocupante' => TipoOcupante::Condutor,
            ],
            [
                'id' => 10504,
                'veiculo_id' => $veiculoMarcos?->id,
                'condutor_id' => $marcos?->id,
                'placa_registro' => 'BRA-2819',
                'cancela' => 'Cancela 03 (Saída)',
                'data_hora_entrada' => Carbon::parse('2026-09-21 18:42:05'),
                'data_hora_saida' => Carbon::parse('2026-09-21 22:35:10'),
                'status' => StatusAcesso::Finalizado,
                'operador_id' => $guardaSilva?->id,
                'justificativa' => null,
                'tipo_ocupante' => TipoOcupante::Condutor,
            ],
            // Terça-feira (15/09/2026 e 22/09/2026) - Profa. Dra. Juliana Rezende
            [
                'id' => 10480,
                'veiculo_id' => $veiculoJuliana?->id,
                'condutor_id' => $juliana?->id,
                'placa_registro' => 'GTR-4C88',
                'cancela' => 'Cancela 01 (Principal)',
                'data_hora_entrada' => Carbon::parse('2026-09-15 14:10:12'),
                'data_hora_saida' => null,
                'status' => StatusAcesso::Autorizado,
                'operador_id' => $guardaSilva?->id,
                'justificativa' => null,
                'tipo_ocupante' => TipoOcupante::Condutor,
            ],
            [
                'id' => 10485,
                'veiculo_id' => $veiculoJuliana?->id,
                'condutor_id' => $juliana?->id,
                'placa_registro' => 'GTR-4C88',
                'cancela' => 'Cancela 01 (Principal)',
                'data_hora_entrada' => Carbon::parse('2026-09-22 14:14:02'),
                'data_hora_saida' => null,
                'status' => StatusAcesso::Autorizado,
                'operador_id' => $guardaRibeiro?->id,
                'justificativa' => null,
                'tipo_ocupante' => TipoOcupante::Condutor,
            ],
            // Quarta-feira (16/09/2026) - Carlos Silva
            [
                'id' => 10475,
                'veiculo_id' => $veiculoCarlos?->id,
                'condutor_id' => $carlos?->id,
                'placa_registro' => 'ABC-1234',
                'cancela' => 'Cancela 01 (Principal)',
                'data_hora_entrada' => Carbon::parse('2026-09-16 14:28:40'),
                'data_hora_saida' => null,
                'status' => StatusAcesso::Autorizado,
                'operador_id' => $guardaRibeiro?->id,
                'justificativa' => null,
                'tipo_ocupante' => TipoOcupante::Condutor,
            ],
            // Quinta-feira (17/09/2026) - Prof. Me. André Cavalcante
            [
                'id' => 10470,
                'veiculo_id' => $veiculoAndre?->id,
                'condutor_id' => $andre?->id,
                'placa_registro' => 'KPL-3390',
                'cancela' => 'Cancela 03 (Saída)',
                'data_hora_entrada' => Carbon::parse('2026-09-17 13:45:00'),
                'data_hora_saida' => Carbon::parse('2026-09-17 18:15:00'),
                'status' => StatusAcesso::Finalizado,
                'operador_id' => $guardaSilva?->id,
                'justificativa' => null,
                'tipo_ocupante' => TipoOcupante::Condutor,
            ],

            // Registros de suporte aos testes e monitoramento em tempo real
            [
                'id' => 1082,
                'veiculo_id' => null,
                'condutor_id' => null,
                'placa_registro' => RegistroAcesso::PLACA_NAO_IDENTIFICADA,
                'cancela' => 'Cancela 01 (Principal)',
                'data_hora_entrada' => now()->subMinutes(15),
                'data_hora_saida' => null,
                'status' => StatusAcesso::NaoCadastrado,
                'operador_id' => $guardaSilva?->id,
                'justificativa' => 'Aguardando validação manual',
                'tipo_ocupante' => TipoOcupante::Condutor,
            ],
            [
                'id' => 1083,
                'veiculo_id' => $veiculoMarcos?->id,
                'condutor_id' => $marcos?->id,
                'placa_registro' => 'BRA-2819',
                'cancela' => 'Cancela 01 (Principal)',
                'data_hora_entrada' => now()->subMinutes(10),
                'data_hora_saida' => null,
                'status' => StatusAcesso::Autorizado,
                'operador_id' => $guardaSilva?->id,
                'justificativa' => null,
                'tipo_ocupante' => TipoOcupante::Condutor,
            ],
            [
                'id' => 1084,
                'veiculo_id' => null,
                'condutor_id' => null,
                'placa_registro' => 'BRA-2819',
                'cancela' => 'Cancela 01 (Principal)',
                'data_hora_entrada' => now()->subMinutes(5),
                'data_hora_saida' => null,
                'status' => StatusAcesso::Pendente,
                'operador_id' => $guardaSilva?->id,
                'justificativa' => null,
                'tipo_ocupante' => TipoOcupante::Condutor,
            ],
        ];

        foreach ($registros as $dado) {
            RegistroAcesso::query()->updateOrCreate(
                ['id' => $dado['id']],
                $dado
            );
        }
    }
}
