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
        Schema::table('registros_acesso', function (Blueprint $table) {
            $table->enum('tipo_ocupante', ['condutor', 'passageiro'])
                ->default('condutor')
                ->after('condutor_id');
            $table->foreignId('passagem_origem_id')
                ->nullable()
                ->after('tipo_ocupante')
                ->constrained('registros_acesso')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registros_acesso', function (Blueprint $table) {
            $table->dropForeign(['passagem_origem_id']);
            $table->dropColumn(['tipo_ocupante', 'passagem_origem_id']);
        });
    }
};
