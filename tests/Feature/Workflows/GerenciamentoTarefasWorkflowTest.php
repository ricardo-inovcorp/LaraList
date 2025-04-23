<?php

namespace Tests\Feature\Workflows;

use App\Models\Tarefa;
use App\Models\TarefaLog;
use App\Models\User;
use App\Models\Categoria;
use App\Notifications\TarefaAtividadeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class GerenciamentoTarefasWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ciclo_de_vida_completo_de_uma_tarefa()
    {
        // 1. Fake de notificações para evitar envio real
        Notification::fake();
        
        // 2. Criar usuário
        $user = User::factory()->create([
            'name' => 'Usuário de Teste',
            'email' => 'teste@exemplo.com'
        ]);
        
        $this->actingAs($user);
        
        // 3. Criar categoria
        $categoria = Categoria::factory()->create([
            'utilizador_id' => $user->id,
            'nome' => 'Trabalho'
        ]);
        
        // 4. CRIAR uma nova tarefa
        $tarefaData = [
            'titulo' => 'Implementar testes unitários',
            'descricao' => 'Criar testes unitários para o sistema',
            'estado' => 'pendente',
            'prioridade' => 'media',
            'categoria_id' => $categoria->id
        ];
        
        $response = $this->post(route('tarefas.store'), $tarefaData);
        $response->assertRedirect(route('tarefas.index'));
        
        // Verificar se a tarefa foi criada
        $tarefa = Tarefa::where('titulo', 'Implementar testes unitários')->first();
        $this->assertNotNull($tarefa);
        
        // Verificar se o log de criação foi registrado
        $this->assertDatabaseHas('tarefa_logs', [
            'tarefa_id' => $tarefa->id,
            'tipo_acao' => 'criar',
            'descricao' => 'Tarefa criada'
        ]);
        
        // Verificar se a notificação de criação foi enviada
        Notification::assertSentTo($user, TarefaAtividadeNotification::class);
        Notification::assertCount(1);
        
        // 5. ATUALIZAR a tarefa - Definir prioridade alta
        $dadosAtualizados = [
            'titulo' => 'Implementar testes unitários',
            'descricao' => 'Criar testes unitários para o sistema',
            'estado' => 'pendente',
            'prioridade' => 'alta',  // Alterado
            'categoria_id' => $categoria->id
        ];
        
        $response = $this->put(route('tarefas.update', $tarefa), $dadosAtualizados);
        $response->assertRedirect(route('tarefas.index'));
        
        // Verificar se a tarefa foi atualizada
        $tarefa->refresh();
        $this->assertEquals('alta', $tarefa->prioridade);
        
        // Verificar se o log de atualização foi registrado
        $this->assertDatabaseHas('tarefa_logs', [
            'tarefa_id' => $tarefa->id,
            'tipo_acao' => 'atualizar',
            'campo_alterado' => 'prioridade'
        ]);
        
        // 6. ATUALIZAR para EM PROGRESSO
        $dadosAtualizados['estado'] = 'em_progresso';
        
        $response = $this->put(route('tarefas.update', $tarefa), $dadosAtualizados);
        $response->assertRedirect(route('tarefas.index'));
        
        // Verificar se o estado foi alterado
        $tarefa->refresh();
        $this->assertEquals('em_progresso', $tarefa->estado);
        
        // Verificar se o log de mudança de estado foi registrado
        $this->assertDatabaseHas('tarefa_logs', [
            'tarefa_id' => $tarefa->id,
            'tipo_acao' => 'atualizar',
            'campo_alterado' => 'estado',
            'valor_anterior' => 'pendente',
            'valor_novo' => 'em_progresso'
        ]);
        
        // 7. MARCAR COMO CONCLUÍDA
        $response = $this->patch(route('tarefas.toggle-concluida', $tarefa));
        
        // Verificar se o estado foi alterado para concluída
        $tarefa->refresh();
        $this->assertEquals('concluida', $tarefa->estado);
        $this->assertTrue($tarefa->concluida);
        
        // Verificar se o log de conclusão foi registrado
        $this->assertDatabaseHas('tarefa_logs', [
            'tarefa_id' => $tarefa->id,
            'tipo_acao' => 'estado',
            'campo_alterado' => 'estado',
            'valor_anterior' => 'em_progresso',
            'valor_novo' => 'concluida'
        ]);
        
        // 8. EXCLUIR a tarefa
        $tarefaId = $tarefa->id;
        $response = $this->delete(route('tarefas.destroy', $tarefa));
        $response->assertRedirect(route('tarefas.index'));
        
        // Verificar se a tarefa foi excluída
        $this->assertDatabaseMissing('tarefas', [
            'id' => $tarefaId
        ]);
    }
}
