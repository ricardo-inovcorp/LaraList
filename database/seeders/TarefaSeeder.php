<?php

namespace Database\Seeders;

use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Database\Seeder;

class TarefaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Garantir que existe pelo menos um utilizador
        if (User::count() === 0) {
            User::factory()->create([
                'name' => 'Utilizador de Teste',
                'email' => 'teste@example.com',
                'password' => bcrypt('password')
            ]);
        }

        // Obter todos os utilizadores
        $users = User::all();

        foreach ($users as $user) {
            // Criar 3 tarefas pendentes com diferentes prioridades
            Tarefa::factory()
                ->count(1)
                ->pendente()
                ->baixaPrioridade()
                ->state(['utilizador_id' => $user->id])
                ->create();
                
            Tarefa::factory()
                ->count(1)
                ->pendente()
                ->mediaPrioridade()
                ->state(['utilizador_id' => $user->id])
                ->create();
                
            Tarefa::factory()
                ->count(1)
                ->pendente()
                ->altaPrioridade()
                ->state(['utilizador_id' => $user->id])
                ->create();
            
            // Criar 2 tarefas em progresso
            Tarefa::factory()
                ->count(2)
                ->emProgresso()
                ->state(['utilizador_id' => $user->id])
                ->create();
            
            // Criar 1 tarefa concluída
            Tarefa::factory()
                ->count(1)
                ->concluida()
                ->state(['utilizador_id' => $user->id])
                ->create();
            
            // Criar 1 tarefa urgente
            Tarefa::factory()
                ->count(1)
                ->urgente()
                ->state(['utilizador_id' => $user->id])
                ->create();
        }
    }
} 