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
        Schema::connection('portal')
            ->table('aniel_agenda_subservicos', function (Blueprint $table) {
            $table->unsignedBigInteger('voalle_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('portal')
            ->table('aniel_agenda_subservicos', function (Blueprint $table) {
            $table->dropColumn('voalle_id');
        });
    }
};
