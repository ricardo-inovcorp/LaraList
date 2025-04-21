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
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilizador_id')->constrained('users')->onDelete('cascade');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->enum('estado', ['pendente', 'em_progresso', 'concluida', 'cancelada'])->default('pendente');
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'urgente'])->default('media');
            $table->string('categoria')->nullable();
            $table->dateTime('data_conclusao')->nullable();
            $table->boolean('concluida')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarefas');
    }
};
