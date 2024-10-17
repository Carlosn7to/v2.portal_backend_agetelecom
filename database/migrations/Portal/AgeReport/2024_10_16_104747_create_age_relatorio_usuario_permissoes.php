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
        Schema::create('age_relatorio_usuario_permissoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('portal_usuarios');
            $table->enum('nivel', ['usuario', 'administrador'])->default('usuario');
            $table->json('relatorios_liberados')->nullable();
            $table->foreignId('liberado_por')->constrained('portal_usuarios');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_relatorio_usuario_permissoes');
    }
};
