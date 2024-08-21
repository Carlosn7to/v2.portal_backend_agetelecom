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
        Schema::connection('healthChecker')->create('aplicacoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255);
            $table->string('descricao')->nullable();
            $table->string('ip');
            $table->string('url', 255)->nullable();
            $table->boolean('publica')->default(false);
            $table->boolean('monitoramento')->default(false);
            $table->enum('status', ['ativa', 'inativa', 'manutencao', 'desenvolvimento'])->default('ativa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aplicacoes');
    }
};
