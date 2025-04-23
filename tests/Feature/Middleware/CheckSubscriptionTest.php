<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_access_to_users_with_active_subscription()
    {
        $user = User::factory()->create();
        
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => 'free',
            'is_active' => true,
            'trial_ends_at' => now()->addDays(14),
        ]);

        $response = $this->actingAs($user)
            ->get(route('tarefas.show', ['tarefa' => 1]));

        $response->assertRedirect(route('subscription.index'));
    }
} 