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
        Schema::connection('portal')->table('aniel_importacao_ordens', function (Blueprint $table) {
            $table->string('criado_por')->after('data_agendamento')->nullable();
            $table->string('setor')->after('criado_por')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('portal')->table('aniel_importacao_ordens', function (Blueprint $table) {
            $table->dropColumn('criado_por');
            $table->dropColumn('setor');
        });
    }
};
