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
        Schema::create('portal_usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255);
            $table->string('login', 255)->unique();
            $table->string('email', 255);
            $table->string('password', 255);
            $table->unsignedBigInteger('setor_id' )->nullable();
            $table->unsignedBigInteger('privilegio_id')->nullable();
            $table->unsignedBigInteger('criado_por');
            $table->unsignedBigInteger('modificado_por');

            $table->timestamps();
            $table->softDeletes();


            $table->foreign('privilegio_id')->references('id')->on('portal_privilegios');
            $table->foreign('setor_id')->references('id')->on('portal_setores');
            $table->foreign('criado_por')->references('id')->on('portal_usuarios');
            $table->foreign('modificado_por')->references('id')->on('portal_usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_usuarios');
    }
};
