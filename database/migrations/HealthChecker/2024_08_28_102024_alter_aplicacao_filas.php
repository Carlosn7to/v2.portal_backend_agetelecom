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
        Schema::connection('healthChecker')->table('aplicacao_filas', function (Blueprint $table) {
            $table->integer('processadas')->after('aplicacao_id')->default(0);
            $table->integer('pendentes')->after('processadas')->default(0);
            $table->integer('erros')->after('pendentes')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
