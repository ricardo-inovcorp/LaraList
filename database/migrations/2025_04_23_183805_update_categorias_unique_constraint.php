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
        Schema::table('categorias', function (Blueprint $table) {
            // Remove a constraint única existente
            $table->dropUnique(['nome']);
            
            // Adiciona uma nova constraint única combinando nome e utilizador_id
            $table->unique(['nome', 'utilizador_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            // Remove a constraint única combinada
            $table->dropUnique(['nome', 'utilizador_id']);
            
            // Restaura a constraint única original
            $table->unique(['nome']);
        });
    }
}; 