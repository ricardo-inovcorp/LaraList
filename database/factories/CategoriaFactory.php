<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categoria>
 */
class CategoriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categorias = ['Trabalho', 'Pessoal', 'Finanças', 'Casa', 'Estudos', 'Saúde', 'Lazer'];
        $cores = ['#FF5733', '#33FF57', '#3357FF', '#F033FF', '#FF33A6', '#33FFF6', '#FFB533'];
        
        return [
            'nome' => $this->faker->randomElement($categorias),
            'cor' => $this->faker->randomElement($cores),
            'utilizador_id' => User::factory(),
        ];
    }
}
