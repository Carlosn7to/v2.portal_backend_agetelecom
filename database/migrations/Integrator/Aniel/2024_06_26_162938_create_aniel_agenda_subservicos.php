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
        Schema::create('aniel_agenda_subservicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servico_id')->constrained('aniel_agenda_servicos');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->foreignId('vinculado_por')->constrained('portal_usuarios');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aniel_agenda_subservicos');
    }
};
