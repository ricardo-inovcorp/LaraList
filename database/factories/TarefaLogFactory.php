<?php

namespace Database\Factories;

use App\Models\Tarefa;
use App\Models\TarefaLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TarefaLog>
 */
class TarefaLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tarefa_id' => Tarefa::factory(),
            'utilizador_id' => User::factory(),
            'tipo_acao' => $this->faker->randomElement(['criar', 'atualizar', 'estado', 'excluir']),
            'campo_alterado' => null,
            'valor_anterior' => null,
            'valor_novo' => null,
            'descricao' => $this->faker->sentence(),
        ];
    }

    /**
     * Define um log para criação de tarefa.
     */
    public function criacao(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_acao' => 'criar',
            'descricao' => 'Tarefa criada',
        ]);
    }

    /**
     * Define um log para alteração de tarefa.
     */
    public function atualizacao($campo = 'titulo', $valorAnterior = 'Antigo título', $valorNovo = 'Novo título'): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_acao' => 'atualizar',
            'campo_alterado' => $campo,
            'valor_anterior' => $valorAnterior,
            'valor_novo' => $valorNovo,
            'descricao' => "Campo {$campo} alterado de '{$valorAnterior}' para '{$valorNovo}'",
        ]);
    }

    /**
     * Define um log para mudança de estado de tarefa.
     */
    public function mudancaEstado($estadoAnterior = 'pendente', $estadoNovo = 'concluida'): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_acao' => 'estado',
            'campo_alterado' => 'estado',
            'valor_anterior' => $estadoAnterior,
            'valor_novo' => $estadoNovo,
            'descricao' => "Estado alterado de '{$estadoAnterior}' para '{$estadoNovo}'",
        ]);
    }

    /**
     * Define um log para exclusão de tarefa.
     */
    public function exclusao(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_acao' => 'excluir',
            'descricao' => 'Tarefa excluída',
        ]);
    }
}
