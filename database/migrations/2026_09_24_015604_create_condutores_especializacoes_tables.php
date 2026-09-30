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
        // 1. Especialização Funcionários
        Schema::create('condutores_funcionarios', function (Blueprint $table) {
            $table->unsignedBigInteger('condutor_id')->primary();
            $table->string('codigo_acesso', 20)->unique();
            $table->string('setor', 100)->nullable();
            $table->timestamps();

            $table->foreign('condutor_id')
                ->references('id')
                ->on('condutores')
                ->cascadeOnDelete();
        });

        // 2. Especialização Professores
        Schema::create('condutores_professores', function (Blueprint $table) {
            $table->unsignedBigInteger('condutor_id')->primary();
            $table->string('codigo_acesso', 20)->unique();
            $table->string('departamento', 100)->nullable();
            $table->timestamps();

            $table->foreign('condutor_id')
                ->references('id')
                ->on('condutores')
                ->cascadeOnDelete();
        });

        // 3. Especialização Prestadores de Serviço
        Schema::create('condutores_prestadores', function (Blueprint $table) {
            $table->unsignedBigInteger('condutor_id')->primary();
            $table->string('empresa', 100);
            $table->date('inicio_contrato')->nullable();
            $table->date('fim_contrato')->nullable();
            $table->timestamps();

            $table->foreign('condutor_id')
                ->references('id')
                ->on('condutores')
                ->cascadeOnDelete();
        });

        // 4. Especialização Visitantes
        Schema::create('condutores_visitantes', function (Blueprint $table) {
            $table->unsignedBigInteger('condutor_id')->primary();
            $table->string('motivo_visita', 255);
            $table->string('setor_destino', 100)->nullable();
            $table->string('permanencia_estimada', 50)->nullable();
            $table->timestamps();

            $table->foreign('condutor_id')
                ->references('id')
                ->on('condutores')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('condutores_visitantes');
        Schema::dropIfExists('condutores_prestadores');
        Schema::dropIfExists('condutores_professores');
        Schema::dropIfExists('condutores_funcionarios');
    }
};
