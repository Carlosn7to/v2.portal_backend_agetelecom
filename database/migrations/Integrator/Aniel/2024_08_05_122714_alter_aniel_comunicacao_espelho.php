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
        Schema::connection('portal')->table('aniel_agenda_espelho', function (Blueprint $table) {
            $table->string('confirmacao_deslocamento')->nullable()->after('confirmacao_cliente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('portal')->table('aniel_agenda_espelho', function (Blueprint $table) {
            $table->dropColumn('confirmacao_deslocamento');
        });
    }
};
