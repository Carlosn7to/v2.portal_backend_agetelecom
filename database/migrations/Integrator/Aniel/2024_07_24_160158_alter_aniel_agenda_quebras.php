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
        Schema::table('aniel_agenda_quebras', function (Blueprint $table) {
            $table->string('localidade', 255)->after('periodo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aniel_agenda_quebras', function (Blueprint $table) {
            $table->dropColumn('localidade');
        });
    }
};
