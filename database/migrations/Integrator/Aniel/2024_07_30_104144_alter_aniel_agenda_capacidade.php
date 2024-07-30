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
        Schema::connection('portal')->table('aniel_agenda_capacidade', function (Blueprint $table) {
            $table->date('data_fechamento')->nullable()->after('motivo_fechamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('portal')->table('aniel_agenda_capacidade', function (Blueprint $table) {
            $table->dropColumn('data_fechamento');
        });
    }
};
