<?php

namespace Tests\Unit\Services;

use App\Models\Tarefa;
use App\Models\User;
use App\Models\TarefaLog;
use App\Services\TarefaLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TarefaAtividadeNotification;
use Tests\TestCase;

class TarefaLogServiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $user;
    protected Tarefa $tarefa;
    protected TarefaLogService $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Desativar notificações para os testes
        Notification::fake();
        
        // Criar usuário para os testes
        $this->user = User::factory()->create();
        
        // Autenticar o usuário
        $this->actingAs($this->user);
        
        // Criar tarefa de teste
        $this->tarefa = Tarefa::factory()->create([
            'utilizador_id' => $this->user->id,
            'titulo' => 'Tarefa de Teste',
            'descricao' => 'Descrição da tarefa de teste',
            'estado' => 'pendente',
            'prioridade' => 'media'
        ]);
        
        // Instanciar o serviço
        $this->service = new TarefaLogService();
    }

    /** @test */
    public function registrar_criacao_cria_log_com_tipo_correto()
    {
        $log = $this->service->registrarCriacao($this->tarefa);
        
        $this->assertInstanceOf(TarefaLog::class, $log);
        $this->assertEquals('criar', $log->tipo_acao);
        $this->assertEquals('Tarefa criada', $log->descricao);
        $this->assertEquals($this->tarefa->id, $log->tarefa_id);
        $this->assertEquals($this->user->id, $log->utilizador_id);
        
        // Verificar se a notificação foi enviada
        Notification::assertSentTo(
            $this->user,
            TarefaAtividadeNotification::class
        );
    }

    /** @test */
    public function registrar_atualizacao_cria_log_para_cada_campo_alterado()
    {
        $originalValues = [
            'titulo' => 'Tarefa de Teste'
        ];
        
        $changes = [
            'titulo' => 'Título Atualizado'
        ];
        
        $logs = $this->service->registrarAtualizacao($this->tarefa, $originalValues, $changes);
        
        $this->assertCount(1, $logs);
        $this->assertEquals('atualizar', $logs[0]->tipo_acao);
        $this->assertEquals('titulo', $logs[0]->campo_alterado);
        $this->assertEquals('Tarefa de Teste', $logs[0]->valor_anterior);
        $this->assertEquals('Título Atualizado', $logs[0]->valor_novo);
        
        // Verificar se a notificação foi enviada
        Notification::assertSentTo(
            $this->user,
            TarefaAtividadeNotification::class
        );
    }

    /** @test */
    public function registrar_mudanca_de_estado_cria_log_com_estados_corretos()
    {
        $log = $this->service->registrarMudancaEstado($this->tarefa, 'pendente', 'concluida');
        
        $this->assertInstanceOf(TarefaLog::class, $log);
        $this->assertEquals('estado', $log->tipo_acao);
        $this->assertEquals('estado', $log->campo_alterado);
        $this->assertEquals('pendente', $log->valor_anterior);
        $this->assertEquals('concluida', $log->valor_novo);
        $this->assertStringContainsString("Estado alterado de 'pendente' para 'concluida'", $log->descricao);
        
        // Verificar se a notificação foi enviada
        Notification::assertSentTo(
            $this->user,
            TarefaAtividadeNotification::class
        );
    }

    /** @test */
    public function registrar_exclusao_cria_log_com_tipo_correto()
    {
        $log = $this->service->registrarExclusao($this->tarefa);
        
        $this->assertInstanceOf(TarefaLog::class, $log);
        $this->assertEquals('excluir', $log->tipo_acao);
        $this->assertEquals('Tarefa excluída', $log->descricao);
        $this->assertEquals($this->tarefa->id, $log->tarefa_id);
        
        // Verificar se a notificação foi enviada
        Notification::assertSentTo(
            $this->user,
            TarefaAtividadeNotification::class
        );
    }
}
