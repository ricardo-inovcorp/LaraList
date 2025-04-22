<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Tarefa;
use App\Models\TarefaLog;

class TarefaAtividadeNotification extends Notification
{
    use Queueable;

    /**
     * O log de atividade da tarefa.
     */
    protected TarefaLog $log;

    /**
     * A tarefa relacionada.
     */
    protected Tarefa $tarefa;

    /**
     * Create a new notification instance.
     */
    public function __construct(TarefaLog $log, Tarefa $tarefa)
    {
        $this->log = $log;
        $this->tarefa = $tarefa;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Somente canal de email para entrega imediata
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $tipoAcao = $this->getTipoAcaoFormatado();
        $titulo = $this->getTituloEmail();
        
        $mail = (new MailMessage)
            ->subject("LaraList - {$tipoAcao} em tarefa: {$this->tarefa->titulo}")
            ->greeting("Olá {$notifiable->name}!")
            ->line($titulo)
            ->line($this->log->descricao);

        // Adicionar link para visualizar a tarefa (exceto quando excluída)
        if ($this->log->tipo_acao !== 'excluir') {
            $mail->action('Ver Detalhes', url("/tarefas/{$this->tarefa->id}"));
        }

        // Adicionar detalhes da tarefa
        $mail->line('**Detalhes da Tarefa:**')
            ->line("**Título:** {$this->tarefa->titulo}")
            ->line("**Estado:** " . $this->getEstadoFormatado($this->tarefa->estado))
            ->line("**Prioridade:** " . $this->getPrioridadeFormatada($this->tarefa->prioridade));

        // Adicionar data e hora da atividade
        $mail->line("Esta atividade foi registrada em " . $this->log->created_at->format('d/m/Y \à\s H:i:s'));
        
        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tarefa_id' => $this->tarefa->id,
            'log_id' => $this->log->id,
            'tipo_acao' => $this->log->tipo_acao,
            'descricao' => $this->log->descricao,
        ];
    }

    /**
     * Obter a formatação amigável do tipo de ação para o email.
     */
    protected function getTipoAcaoFormatado(): string
    {
        $tipos = [
            'criar' => 'Criação',
            'atualizar' => 'Atualização',
            'estado' => 'Mudança de estado',
            'excluir' => 'Exclusão',
        ];

        return $tipos[$this->log->tipo_acao] ?? 'Atividade';
    }

    /**
     * Obter o título para o email baseado no tipo de ação.
     */
    protected function getTituloEmail(): string
    {
        switch ($this->log->tipo_acao) {
            case 'criar':
                return "Uma nova tarefa foi criada no seu LaraList.";
            case 'atualizar':
                return "Uma de suas tarefas foi atualizada no LaraList.";
            case 'estado':
                return "O estado de uma tarefa foi alterado no LaraList.";
            case 'excluir':
                return "Uma tarefa foi excluída do seu LaraList.";
            default:
                return "Houve uma atividade em sua tarefa no LaraList.";
        }
    }

    /**
     * Obter a formatação do estado da tarefa.
     */
    protected function getEstadoFormatado(string $estado): string
    {
        $estados = [
            'pendente' => 'Pendente',
            'em_progresso' => 'Em Progresso',
            'concluida' => 'Concluída',
            'cancelada' => 'Cancelada',
        ];

        return $estados[$estado] ?? $estado;
    }

    /**
     * Obter a formatação da prioridade da tarefa.
     */
    protected function getPrioridadeFormatada(string $prioridade): string
    {
        $prioridades = [
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'urgente' => 'Urgente',
        ];

        return $prioridades[$prioridade] ?? $prioridade;
    }
}
