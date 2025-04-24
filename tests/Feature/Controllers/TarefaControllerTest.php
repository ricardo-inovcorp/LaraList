<?php

namespace Tests\Feature\Controllers;

use App\Models\Tarefa;
use App\Models\TarefaLog;
use App\Models\User;
use App\Models\Categoria;
use App\Notifications\TarefaAtividadeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\Models\Subscription;

class TarefaControllerTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $user;
    protected Categoria $categoria;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Criar usuário para testes
        $this->user = User::factory()->create();
        
        // Criar categoria para testes
        $this->categoria = Categoria::factory()->create([
            'utilizador_id' => $this->user->id,
            'nome' => 'Trabalho'
        ]);
        
        // Fake notifications para evitar envio real
        Notification::fake();
        
        // Criar subscrição ativa para o usuário
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_type' => 'free',
            'is_active' => true,
            'trial_ends_at' => now()->addDays(14),
        ]);
    }
    
    /** @test */
    public function usuario_nao_autenticado_redireciona_para_login()
    {
        // Tentar acessar a listagem sem estar autenticado
        $response = $this->get(route('tarefas.index'));
        
        // Verificar se é redirecionado para login
        $response->assertRedirect(route('login'));
    }
    
    /** @test */
    public function usuario_autenticado_pode_ver_listagem_de_tarefas()
    {
        // Criar algumas tarefas para o usuário
        Tarefa::factory()->count(3)->create([
            'utilizador_id' => $this->user->id
        ]);
        
        // Autenticar usuário e acessar listagem
        $response = $this->actingAs($this->user)
            ->get(route('tarefas.index'));
        
        // Verificar se a página foi carregada com sucesso
        $response->assertStatus(200);
        
        // Verificar se a view correta foi retornada
        $response->assertInertia(fn ($page) => 
            $page->component('Tarefas/Index')
            ->has('tarefas.data', 3)
        );
    }
    
    /** @test */
    public function usuario_pode_criar_nova_tarefa()
    {
        // Autenticar usuário
        $this->actingAs($this->user);
        
        // Dados para criar uma nova tarefa
        $tarefaData = [
            'titulo' => 'Nova Tarefa de Teste',
            'descricao' => 'Descrição da tarefa de teste',
            'estado' => 'pendente',
            'prioridade' => 'media',
            'categoria_id' => $this->categoria->id
        ];
        
        // Enviar requisição para criar tarefa
        $response = $this->post(route('tarefas.store'), $tarefaData);
        
        // Verificar se foi redirecionado após criação
        $response->assertRedirect(route('tarefas.index'));
        $response->assertSessionHas('mensagem', 'Tarefa criada com sucesso!');
        
        // Verificar se a tarefa foi salva no banco
        $this->assertDatabaseHas('tarefas', [
            'titulo' => 'Nova Tarefa de Teste',
            'utilizador_id' => $this->user->id
        ]);
        
        // Verificar se foi criado um log de criação
        $this->assertDatabaseHas('tarefa_logs', [
            'tipo_acao' => 'criar',
            'descricao' => 'Tarefa criada'
        ]);
        
        // Verificar se foi enviada uma notificação
        Notification::assertSentTo(
            $this->user,
            TarefaAtividadeNotification::class
        );
    }
    
    /** @test */
    public function usuario_pode_atualizar_tarefa()
    {
        // Autenticar usuário
        $this->actingAs($this->user);
        
        // Criar uma tarefa para atualizar
        $tarefa = Tarefa::factory()->create([
            'utilizador_id' => $this->user->id,
            'titulo' => 'Tarefa Original',
            'descricao' => 'Descrição original',
            'estado' => 'pendente',
            'prioridade' => 'baixa'
        ]);
        
        // Dados para atualizar a tarefa
        $dadosAtualizados = [
            'titulo' => 'Tarefa Atualizada',
            'descricao' => 'Descrição atualizada',
            'estado' => 'em_progresso',
            'prioridade' => 'alta',
            'categoria_id' => $this->categoria->id
        ];
        
        // Enviar requisição para atualizar tarefa
        $response = $this->put(route('tarefas.update', $tarefa), $dadosAtualizados);
        
        // Verificar se foi redirecionado após atualização
        $response->assertRedirect(route('tarefas.index'));
        $response->assertSessionHas('mensagem', 'Tarefa atualizada com sucesso!');
        
        // Verificar se a tarefa foi atualizada no banco
        $this->assertDatabaseHas('tarefas', [
            'id' => $tarefa->id,
            'titulo' => 'Tarefa Atualizada',
            'estado' => 'em_progresso',
            'prioridade' => 'alta'
        ]);
        
        // Verificar se foram criados logs de atualização
        $this->assertDatabaseHas('tarefa_logs', [
            'tarefa_id' => $tarefa->id,
            'tipo_acao' => 'atualizar',
            'campo_alterado' => 'titulo',
            'valor_anterior' => 'Tarefa Original',
            'valor_novo' => 'Tarefa Atualizada'
        ]);
        
        // Verificar se foi enviada uma notificação
        Notification::assertSentTo(
            $this->user,
            TarefaAtividadeNotification::class
        );
    }
    
    /** @test */
    public function usuario_pode_marcar_tarefa_como_concluida()
    {
        // Autenticar usuário
        $this->actingAs($this->user);
        
        // Criar uma tarefa pendente
        $tarefa = Tarefa::factory()->pendente()->create([
            'utilizador_id' => $this->user->id
        ]);
        
        // Enviar requisição para marcar como concluída
        $response = $this->patch(route('tarefas.toggle-concluida', $tarefa));
        
        // Verificar se foi redirecionado após a operação
        $response->assertSessionHas('mensagem', 'Estado da tarefa alterado com sucesso!');
        
        // Verificar se a tarefa foi marcada como concluída
        $this->assertDatabaseHas('tarefas', [
            'id' => $tarefa->id,
            'estado' => 'concluida',
            'concluida' => true
        ]);
        
        // Verificar se foi criado um log de mudança de estado
        $this->assertDatabaseHas('tarefa_logs', [
            'tarefa_id' => $tarefa->id,
            'tipo_acao' => 'estado',
            'campo_alterado' => 'estado',
            'valor_anterior' => 'pendente',
            'valor_novo' => 'concluida'
        ]);
        
        // Verificar se foi enviada uma notificação
        Notification::assertSentTo(
            $this->user,
            TarefaAtividadeNotification::class
        );
    }
    
    /** @test */
    public function usuario_pode_excluir_tarefa()
    {
        // Autenticar usuário
        $this->actingAs($this->user);
        
        // Criar uma tarefa para excluir
        $tarefa = Tarefa::factory()->create([
            'utilizador_id' => $this->user->id,
            'titulo' => 'Tarefa para Excluir'
        ]);
        
        // Salvar o ID da tarefa antes de excluir
        $tarefaId = $tarefa->id;
        
        // Enviar requisição para excluir tarefa
        $response = $this->delete(route('tarefas.destroy', $tarefa));
        
        // Verificar se foi redirecionado após exclusão
        $response->assertRedirect(route('tarefas.index'));
        $response->assertSessionHas('mensagem', 'Tarefa eliminada com sucesso!');
        
        // Verificar se a tarefa foi removida do banco
        $this->assertDatabaseMissing('tarefas', [
            'id' => $tarefaId
        ]);
    }
    
    /** @test */
    public function usuario_nao_pode_acessar_tarefas_de_outros_usuarios()
    {
        // Autenticar usuário
        $this->actingAs($this->user);
        
        // Criar outro usuário
        $outroUsuario = User::factory()->create();
        
        // Criar uma tarefa para o outro usuário
        $tarefaDeOutroUsuario = Tarefa::factory()->create([
            'utilizador_id' => $outroUsuario->id,
            'titulo' => 'Tarefa de Outro Usuário'
        ]);
        
        // Tentar acessar a tarefa de outro usuário
        $response = $this->get(route('tarefas.show', $tarefaDeOutroUsuario));
        
        // Verificar se o acesso foi negado
        $response->assertStatus(403);
    }
    
    /** @test */
    public function usuario_sem_subscricao_nao_pode_acessar_tarefas()
    {
        // Remover subscrição do usuário
        Subscription::where('user_id', $this->user->id)->delete();
        
        // Autenticar usuário
        $this->actingAs($this->user);
        
        // Tentar acessar a listagem de tarefas
        $response = $this->get(route('tarefas.index'));
        
        // Verificar se é redirecionado para página de subscrição
        $response->assertRedirect(route('subscription.index'));
    }
    
    /** @test */
    public function usuario_com_subscricao_expirada_nao_pode_acessar_tarefas()
    {
        // Atualizar subscrição para expirada
        Subscription::where('user_id', $this->user->id)->update([
            'is_active' => false,
            'trial_ends_at' => now()->subDays(1),
            'subscription_ends_at' => now()->subDays(1)
        ]);
        
        // Autenticar usuário
        $this->actingAs($this->user);
        
        // Tentar acessar a listagem de tarefas
        $response = $this->get(route('tarefas.index'));
        
        // Verificar se é redirecionado para página de subscrição
        $response->assertRedirect(route('subscription.index'));
    }
    
    /** @test */
    public function usuario_em_trial_pode_acessar_tarefas()
    {
        // Atualizar subscrição para período de trial
        Subscription::where('user_id', $this->user->id)->update([
            'is_active' => true,
            'trial_ends_at' => now()->addDays(7),
            'subscription_ends_at' => null
        ]);
        
        // Autenticar usuário
        $this->actingAs($this->user);
        
        // Tentar acessar a listagem de tarefas
        $response = $this->get(route('tarefas.index'));
        
        // Verificar se consegue acessar normalmente
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Tarefas/Index')
        );
    }
}
