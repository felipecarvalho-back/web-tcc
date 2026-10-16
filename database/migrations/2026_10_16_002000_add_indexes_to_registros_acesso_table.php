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
            $table->index(['data_hora_entrada', 'status'], 'idx_registros_entrada_status');
            $table->index('placa_registro', 'idx_registros_placa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registros_acesso', function (Blueprint $table) {
            $table->dropIndex('idx_registros_entrada_status');
            $table->dropIndex('idx_registros_placa');
        });
    }
};
