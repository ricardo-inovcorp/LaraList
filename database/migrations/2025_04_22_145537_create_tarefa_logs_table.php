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
        Schema::create('tarefa_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarefa_id')->constrained()->onDelete('cascade');
            $table->foreignId('utilizador_id')->constrained('users')->onDelete('cascade');
            $table->string('tipo_acao'); // create, update, delete, estado, etc.
            $table->string('campo_alterado')->nullable(); // Qual campo foi alterado (para updates)
            $table->text('valor_anterior')->nullable(); // Valor anterior (para updates)
            $table->text('valor_novo')->nullable(); // Novo valor (para updates)
            $table->text('descricao'); // Descrição da ação em formato legível
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarefa_logs');
    }
};
