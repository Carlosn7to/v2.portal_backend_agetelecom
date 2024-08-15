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
        Schema::table('aniel_agenda_capacidade', function (Blueprint $table) {
            $table->double('contingente')->nullable()->after('utilizado');
            $table->double('contingente_servicos')->nullable()->after('contingente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aniel_agenda_capacidade', function (Blueprint $table) {
            $table->dropColumn('contingente');
            $table->dropColumn('contingente_servicos');
        });
    }
};
