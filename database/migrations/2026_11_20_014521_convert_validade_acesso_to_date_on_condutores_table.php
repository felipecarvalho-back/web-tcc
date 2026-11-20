<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Normaliza valores legados antes de alterar o tipo da coluna (essencial para PostgreSQL/Supabase)
        $condutores = DB::table('condutores')
            ->select(['id', 'validade_acesso'])
            ->get();

        foreach ($condutores as $condutor) {
            $valor = is_string($condutor->validade_acesso) ? trim($condutor->validade_acesso) : '';
            $dataNormalizada = null;

            if ($valor !== '' && strcasecmp($valor, 'Indeterminado') !== 0) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}/', $valor)) {
                    try {
                        $dataNormalizada = Carbon::parse($valor)->format('Y-m-d');
                    } catch (Throwable) {
                        $dataNormalizada = null;
                    }
                } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valor)) {
                    try {
                        $dataNormalizada = Carbon::createFromFormat('d/m/Y', $valor)->format('Y-m-d');
                    } catch (Throwable) {
                        $dataNormalizada = null;
                    }
                }
            }

            DB::table('condutores')
                ->where('id', $condutor->id)
                ->update(['validade_acesso' => $dataNormalizada]);
        }

        // 2. Altera o tipo da coluna para DATE nullable de forma compatível com PostgreSQL (Supabase), SQLite e MySQL
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE condutores ALTER COLUMN validade_acesso DROP DEFAULT');
            DB::statement("ALTER TABLE condutores ALTER COLUMN validade_acesso TYPE DATE USING NULLIF(TRIM(validade_acesso), '')::date");
            DB::statement('ALTER TABLE condutores ALTER COLUMN validade_acesso SET DEFAULT NULL');
        } else {
            Schema::table('condutores', function (Blueprint $table) {
                $table->date('validade_acesso')->nullable()->default(null)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE condutores ALTER COLUMN validade_acesso DROP DEFAULT');
            DB::statement("ALTER TABLE condutores ALTER COLUMN validade_acesso TYPE VARCHAR(50) USING COALESCE(TO_CHAR(validade_acesso, 'YYYY-MM-DD'), 'Indeterminado')");
            DB::statement("ALTER TABLE condutores ALTER COLUMN validade_acesso SET DEFAULT 'Indeterminado'");
        } else {
            Schema::table('condutores', function (Blueprint $table) {
                $table->string('validade_acesso', 50)->nullable()->default('Indeterminado')->change();
            });
        }
    }
};
