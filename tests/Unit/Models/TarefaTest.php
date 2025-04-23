<?php

namespace Tests\Unit\Models;

use App\Models\Tarefa;
use App\Models\User;
use App\Models\Categoria;
use App\Models\TarefaLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TarefaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function tarefa_belongs_to_user()
    {
        $user = User::factory()->create();
        $tarefa = Tarefa::factory()->create([
            'utilizador_id' => $user->id
        ]);
        
        $this->assertInstanceOf(User::class, $tarefa->utilizador);
        $this->assertEquals($user->id, $tarefa->utilizador->id);
    }

    /** @test */
    public function tarefa_can_have_a_category()
    {
        $user = User::factory()->create();
        $categoria = Categoria::factory()->create([
            'utilizador_id' => $user->id
        ]);
        
        $tarefa = Tarefa::factory()->create([
            'utilizador_id' => $user->id,
            'categoria_id' => $categoria->id
        ]);
        
        $this->assertInstanceOf(Categoria::class, $tarefa->categoria);
        $this->assertEquals($categoria->id, $tarefa->categoria->id);
    }

    /** @test */
    public function tarefa_has_many_logs()
    {
        $tarefa = Tarefa::factory()->create();
        
        $log1 = TarefaLog::factory()->create([
            'tarefa_id' => $tarefa->id,
            'tipo_acao' => 'criar'
        ]);
        
        $log2 = TarefaLog::factory()->create([
            'tarefa_id' => $tarefa->id,
            'tipo_acao' => 'atualizar'
        ]);
        
        $this->assertCount(2, $tarefa->logs);
        $this->assertTrue($tarefa->logs->contains($log1));
        $this->assertTrue($tarefa->logs->contains($log2));
    }

    /** @test */
    public function pending_task_has_correct_state()
    {
        $tarefa = Tarefa::factory()->pendente()->create();
        
        $this->assertEquals('pendente', $tarefa->estado);
        $this->assertFalse($tarefa->concluida);
    }

    /** @test */
    public function completed_task_has_correct_state()
    {
        $tarefa = Tarefa::factory()->concluida()->create();
        
        $this->assertEquals('concluida', $tarefa->estado);
        $this->assertTrue($tarefa->concluida);
    }

    /** @test */
    public function in_progress_task_has_correct_state()
    {
        $tarefa = Tarefa::factory()->emProgresso()->create();
        
        $this->assertEquals('em_progresso', $tarefa->estado);
        $this->assertFalse($tarefa->concluida);
    }

    /** @test */
    public function cancelled_task_has_correct_state()
    {
        $tarefa = Tarefa::factory()->cancelada()->create();
        
        $this->assertEquals('cancelada', $tarefa->estado);
        $this->assertFalse($tarefa->concluida);
    }
}
