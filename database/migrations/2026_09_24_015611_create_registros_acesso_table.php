<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registros_acesso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->nullable()->constrained('veiculos')->nullOnDelete();
            $table->foreignId('condutor_id')->nullable()->constrained('condutores')->nullOnDelete();
            $table->string('placa_registro', 10);

            // Dados da entrada
            $table->string('cancela', 50)->default('Cancela 01 (Principal)');
            $table->timestamp('data_hora_entrada')->useCurrent();
            $table->foreignId('operador_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('foto_entrada_path', 255)->nullable();
            $table->string('placa_corrigida', 10)->nullable();

            // Dados da saída
            $table->timestamp('data_hora_saida')->nullable();

            // Status e auditoria
            $table->enum('status', [
                'pendente',
                'nao_cadastrado',
                'autorizado',
                'em_patio',
                'finalizado',
                'bloqueado',
                'liberado_manual',
            ])->default('em_patio');
            $table->string('justificativa', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros_acesso');
    }
};
