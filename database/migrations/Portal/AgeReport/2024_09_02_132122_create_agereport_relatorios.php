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
        Schema::connection('portal')->create('age_relatorios', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('area')->default('Base');
            $table->enum('tipo', ['relatorio', 'dashboard']);
            $table->string('descricao')->nullable();
            $table->text('consulta')->nullable();
            $table->text('iframe')->nullable();
            $table->json('filtros')->nullable();
            $table->string('conexao')->nullable();
            $table->foreignId('criado_por')->constrained('portal_usuarios');
            $table->foreignId('atualizado_por')->constrained('portal_usuarios');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('portal')->dropIfExists('age_relatorios');
    }
};
