<?php

namespace Database\Factories;

use App\Models\Tarefa;
use App\Models\User;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tarefa>
 */
class TarefaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tarefa::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $estados = ['pendente', 'em_progresso', 'concluida', 'cancelada'];
        $prioridades = ['baixa', 'media', 'alta', 'urgente'];
        
        $estado = $this->faker->randomElement($estados);
        $concluida = $estado === 'concluida';
        
        return [
            'utilizador_id' => User::factory(),
            'titulo' => $this->faker->sentence(rand(3, 8)),
            'descricao' => $this->faker->paragraph(rand(2, 5)),
            'estado' => $estado,
            'prioridade' => $this->faker->randomElement($prioridades),
            'categoria_id' => null, // Pode ser associada a uma categoria depois
            'data_conclusao' => $this->faker->dateTimeBetween('now', '+2 months'),
            'concluida' => $concluida,
        ];
    }
    
    /**
     * Indica que a tarefa está pendente.
     */
    public function pendente(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'pendente',
            'concluida' => false,
        ]);
    }
    
    /**
     * Indica que a tarefa está em progresso.
     */
    public function emProgresso(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'em_progresso',
            'concluida' => false,
        ]);
    }
    
    /**
     * Indica que a tarefa está concluída.
     */
    public function concluida(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'concluida',
            'concluida' => true,
            'data_conclusao' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }
    
    /**
     * Indica que a tarefa foi cancelada.
     */
    public function cancelada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'cancelada',
            'concluida' => false,
        ]);
    }
    
    /**
     * Define a prioridade da tarefa como baixa.
     */
    public function baixaPrioridade(): static
    {
        return $this->state(fn (array $attributes) => [
            'prioridade' => 'baixa',
        ]);
    }
    
    /**
     * Define a prioridade da tarefa como média.
     */
    public function mediaPrioridade(): static
    {
        return $this->state(fn (array $attributes) => [
            'prioridade' => 'media',
        ]);
    }
    
    /**
     * Define a prioridade da tarefa como alta.
     */
    public function altaPrioridade(): static
    {
        return $this->state(fn (array $attributes) => [
            'prioridade' => 'alta',
        ]);
    }
    
    /**
     * Define a prioridade da tarefa como urgente.
     */
    public function urgente(): static
    {
        return $this->state(fn (array $attributes) => [
            'prioridade' => 'urgente',
        ]);
    }

    /**
     * Associa a tarefa a uma categoria.
     */
    public function comCategoria(?Categoria $categoria = null): static
    {
        if ($categoria) {
            return $this->state(fn (array $attributes) => [
                'categoria_id' => $categoria->id,
            ]);
        }

        // Se nenhuma categoria for fornecida, cria uma nova
        return $this->state(function (array $attributes) {
            // Obtém o ID do usuário da tarefa
            $userId = $attributes['utilizador_id'];
            if (is_callable($userId)) {
                $user = User::factory()->create();
                $userId = $user->id;
            }
            
            // Cria uma categoria para o mesmo usuário
            $categoria = Categoria::factory()->create([
                'utilizador_id' => $userId
            ]);
            
            return [
                'categoria_id' => $categoria->id,
            ];
        });
    }
} 