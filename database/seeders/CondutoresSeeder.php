<?php

namespace Database\Seeders;

use App\Models\Condutor;
use App\Models\CondutorPrestador;
use App\Models\CondutorProfessor;
use App\Models\CondutorVisitante;
use App\Models\Veiculo;
use Illuminate\Database\Seeder;

class CondutoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Prof. Dr. Marcos Souza (DSM)
        $marcos = Condutor::query()->updateOrCreate(
            ['cpf' => '111.222.333-01'],
            [
                'nome' => 'Prof. Dr. Marcos Souza',
                'tipo' => 'professor',
                'validade_acesso' => now()->addYear(),
                'ativo' => true,
            ]
        );

        CondutorProfessor::query()->updateOrCreate(
            ['condutor_id' => $marcos->id],
            [
                'codigo_acesso' => 'DOC-88312',
                'departamento' => 'DSM',
            ]
        );

        Veiculo::query()->updateOrCreate(
            ['placa' => 'BRA-2819'],
            [
                'condutor_id' => $marcos->id,
                'modelo' => 'Toyota Corolla',
                'cor' => 'Prata',
                'ativo' => true,
            ]
        );

        // 2. Profa. Dra. Juliana Rezende (GTI)
        $juliana = Condutor::query()->updateOrCreate(
            ['cpf' => '111.222.333-02'],
            [
                'nome' => 'Profa. Dra. Juliana Rezende',
                'tipo' => 'professor',
                'validade_acesso' => now()->addYear(),
                'ativo' => true,
            ]
        );

        CondutorProfessor::query()->updateOrCreate(
            ['condutor_id' => $juliana->id],
            [
                'codigo_acesso' => 'DOC-94281',
                'departamento' => 'GTI',
            ]
        );

        Veiculo::query()->updateOrCreate(
            ['placa' => 'GTR-4C88'],
            [
                'condutor_id' => $juliana->id,
                'modelo' => 'Honda Civic',
                'cor' => 'Preto',
                'ativo' => true,
            ]
        );

        // 3. Prof. Me. André Cavalcante (IA / BD)
        $andre = Condutor::query()->updateOrCreate(
            ['cpf' => '111.222.333-03'],
            [
                'nome' => 'Prof. Me. André Cavalcante',
                'tipo' => 'professor',
                'validade_acesso' => now()->addYear(),
                'ativo' => true,
            ]
        );

        CondutorProfessor::query()->updateOrCreate(
            ['condutor_id' => $andre->id],
            [
                'codigo_acesso' => 'DOC-74192',
                'departamento' => 'IA / BD',
            ]
        );

        Veiculo::query()->updateOrCreate(
            ['placa' => 'KPL-3390'],
            [
                'condutor_id' => $andre->id,
                'modelo' => 'VW T-Cross',
                'cor' => 'Branco',
                'ativo' => true,
            ]
        );

        // 4. Carlos Silva (Prestador de Serviços - Manutenção Predial)
        $carlos = Condutor::query()->updateOrCreate(
            ['cpf' => '111.222.333-04'],
            [
                'nome' => 'Carlos Silva',
                'tipo' => 'prestador',
                'validade_acesso' => now()->addMonths(6),
                'ativo' => true,
            ]
        );

        CondutorPrestador::query()->updateOrCreate(
            ['condutor_id' => $carlos->id],
            [
                'codigo_acesso' => 'PRE-102',
                'empresa' => 'Manutenção Predial',
                'inicio_contrato' => now()->subMonth(),
                'fim_contrato' => now()->addMonths(5),
            ]
        );

        Veiculo::query()->updateOrCreate(
            ['placa' => 'ABC-1234'],
            [
                'condutor_id' => $carlos->id,
                'modelo' => 'Fiat Fiorino',
                'cor' => 'Branco',
                'ativo' => true,
            ]
        );

        // 5. Mariana Silveira (Visitante)
        $mariana = Condutor::query()->updateOrCreate(
            ['cpf' => '111.222.333-05'],
            [
                'nome' => 'Mariana Silveira',
                'tipo' => 'visitante',
                'validade_acesso' => now()->addDay(),
                'ativo' => true,
            ]
        );

        CondutorVisitante::query()->updateOrCreate(
            ['condutor_id' => $mariana->id],
            [
                'motivo_visita' => 'Reunião de Coordenação',
                'setor_destino' => 'Coordenação DSM',
                'permanencia_estimada' => '2 horas',
            ]
        );

        Veiculo::query()->updateOrCreate(
            ['placa' => 'FTC-8821'],
            [
                'condutor_id' => $mariana->id,
                'modelo' => 'Chevrolet Onix',
                'cor' => 'Vermelho',
                'ativo' => true,
            ]
        );
    }
}
