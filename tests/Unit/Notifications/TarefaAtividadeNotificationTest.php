<?php

namespace Tests\Unit\Notifications;

use App\Models\Tarefa;
use App\Models\TarefaLog;
use App\Models\User;
use App\Notifications\TarefaAtividadeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class TarefaAtividadeNotificationTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $user;
    protected Tarefa $tarefa;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar usuário para teste
        $this->user = User::factory()->create([
            'name' => 'Usuário de Teste',
            'email' => 'teste@exemplo.com'
        ]);
        
        // Criar tarefa para teste
        $this->tarefa = Tarefa::factory()->create([
            'utilizador_id' => $this->user->id,
            'titulo' => 'Tarefa para Teste de Email',
            'descricao' => 'Descrição da tarefa para teste',
            'estado' => 'pendente',
            'prioridade' => 'alta'
        ]);
    }

    /** @test */
    public function notificacao_usa_canal_de_email()
    {
        $log = TarefaLog::factory()->criacao()->create([
            'tarefa_id' => $this->tarefa->id,
            'utilizador_id' => $this->user->id
        ]);
        
        $notification = new TarefaAtividadeNotification($log, $this->tarefa);
        
        $this->assertEquals(['mail'], $notification->via($this->user));
    }

    /** @test */
    public function email_contem_informacoes_corretas_para_criacao_de_tarefa()
    {
        $log = TarefaLog::factory()->criacao()->create([
            'tarefa_id' => $this->tarefa->id,
            'utilizador_id' => $this->user->id
        ]);
        
        $notification = new TarefaAtividadeNotification($log, $this->tarefa);
        $mail = $notification->toMail($this->user);
        
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertStringContainsString('Criação em tarefa', $mail->subject);
        $this->assertStringContainsString($this->tarefa->titulo, $mail->subject);
        $this->assertStringContainsString($this->user->name, $mail->greeting);
        
        // Verificar se há o botão para ver detalhes
        $hasViewButton = collect($mail->actionText)->contains('Ver Detalhes');
        $this->assertTrue($hasViewButton);
    }

    /** @test */
    public function email_contem_informacoes_corretas_para_atualizacao_de_tarefa()
    {
        $log = TarefaLog::factory()->atualizacao('titulo', 'Título Antigo', 'Título Novo')->create([
            'tarefa_id' => $this->tarefa->id,
            'utilizador_id' => $this->user->id
        ]);
        
        $notification = new TarefaAtividadeNotification($log, $this->tarefa);
        $mail = $notification->toMail($this->user);
        
        $this->assertStringContainsString('Atualização em tarefa', $mail->subject);
        $this->assertStringContainsString($this->tarefa->titulo, $mail->subject);
        
        // Verificar se a descrição da atualização está contida
        $descriptionIncluded = false;
        foreach ($mail->introLines as $line) {
            if (strpos($line, 'alterado de') !== false) {
                $descriptionIncluded = true;
                break;
            }
        }
        $this->assertTrue($descriptionIncluded);
    }

    /** @test */
    public function email_contem_informacoes_corretas_para_mudanca_de_estado()
    {
        $log = TarefaLog::factory()->mudancaEstado('pendente', 'concluida')->create([
            'tarefa_id' => $this->tarefa->id,
            'utilizador_id' => $this->user->id
        ]);
        
        $notification = new TarefaAtividadeNotification($log, $this->tarefa);
        $mail = $notification->toMail($this->user);
        
        $this->assertStringContainsString('Mudança de estado em tarefa', $mail->subject);
        $this->assertStringContainsString($this->tarefa->titulo, $mail->subject);
        
        // Verificar se a descrição do estado está contida
        $stateChangeIncluded = false;
        foreach ($mail->introLines as $line) {
            if (strpos($line, 'Estado alterado de') !== false) {
                $stateChangeIncluded = true;
                break;
            }
        }
        $this->assertTrue($stateChangeIncluded);
    }

    /** @test */
    public function email_contem_informacoes_corretas_para_exclusao_de_tarefa()
    {
        $log = TarefaLog::factory()->exclusao()->create([
            'tarefa_id' => $this->tarefa->id,
            'utilizador_id' => $this->user->id
        ]);
        
        $notification = new TarefaAtividadeNotification($log, $this->tarefa);
        $mail = $notification->toMail($this->user);
        
        $this->assertStringContainsString('Exclusão em tarefa', $mail->subject);
        $this->assertStringContainsString($this->tarefa->titulo, $mail->subject);
        
        // Verificar que NÃO há botão para ver detalhes (tarefa excluída)
        $hasViewButton = collect($mail->actionText)->contains('Ver Detalhes');
        $this->assertFalse($hasViewButton);
    }

    /** @test */
    public function notificacao_retorna_array_com_dados_corretos()
    {
        $log = TarefaLog::factory()->criacao()->create([
            'tarefa_id' => $this->tarefa->id,
            'utilizador_id' => $this->user->id
        ]);
        
        $notification = new TarefaAtividadeNotification($log, $this->tarefa);
        $array = $notification->toArray($this->user);
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('tarefa_id', $array);
        $this->assertArrayHasKey('log_id', $array);
        $this->assertArrayHasKey('tipo_acao', $array);
        $this->assertArrayHasKey('descricao', $array);
        $this->assertEquals($this->tarefa->id, $array['tarefa_id']);
        $this->assertEquals($log->id, $array['log_id']);
        $this->assertEquals('criar', $array['tipo_acao']);
        $this->assertEquals('Tarefa criada', $array['descricao']);
    }
}
