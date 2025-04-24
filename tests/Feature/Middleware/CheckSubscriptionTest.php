<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tarefa;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_access_to_users_with_active_subscription()
    {
        // Criar usuário
        $user = User::factory()->create();
        
        // Criar subscrição ativa
        Subscription::create([
            'user_id' => $user->id,
            'plan_type' => 'free',
            'is_active' => true,
            'trial_ends_at' => now()->addDays(14),
        ]);
        
        // Criar uma tarefa para o usuário
        $tarefa = Tarefa::factory()->create([
            'utilizador_id' => $user->id,
            'titulo' => 'Teste'
        ]);
        
        // Tentar acessar com subscrição ativa
        $response = $this->actingAs($user)
            ->get(route('tarefas.show', ['tarefa' => $tarefa->id]));
        
        $response->assertSuccessful();
    }
} 