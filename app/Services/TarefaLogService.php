<?php

namespace App\Services;

use App\Models\Tarefa;
use App\Models\TarefaLog;
use App\Models\User;
use App\Notifications\TarefaAtividadeNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class TarefaLogService
{
    /**
     * Registrar um log de criação de tarefa.
     *
     * @param Tarefa $tarefa A tarefa criada
     * @return TarefaLog O log criado
     */
    public function registrarCriacao(Tarefa $tarefa): TarefaLog
    {
        $log = $this->registrarLog(
            $tarefa,
            'criar',
            null,
            null,
            null,
            'Tarefa criada'
        );

        // Enviar notificação por email
        $this->enviarNotificacaoEmail($log, $tarefa);

        return $log;
    }

    /**
     * Registrar um log de atualização de tarefa.
     *
     * @param Tarefa $tarefa A tarefa atualizada
     * @param array $originalValues Valores originais antes da atualização
     * @param array $changes Alterações realizadas
     * @return array Os logs criados
     */
    public function registrarAtualizacao(Tarefa $tarefa, array $originalValues, array $changes): array
    {
        $logs = [];

        foreach ($changes as $campo => $novoValor) {
            // Ignorar campos que não estamos interessados em registrar
            if (in_array($campo, ['updated_at', 'created_at'])) {
                continue;
            }

            $valorAnterior = $originalValues[$campo] ?? null;
            
            // Formatamos a descrição da mudança
            $descricao = $this->formatarDescricaoMudanca($campo, $valorAnterior, $novoValor);
            
            // Registrar a mudança
            $log = $this->registrarLog(
                $tarefa,
                'atualizar',
                $campo,
                $valorAnterior,
                $novoValor,
                $descricao
            );
            
            $logs[] = $log;
            
            // Enviar notificação por email para cada mudança
            $this->enviarNotificacaoEmail($log, $tarefa);
        }

        return $logs;
    }

    /**
     * Registrar um log de mudança de estado da tarefa.
     *
     * @param Tarefa $tarefa A tarefa
     * @param string $estadoAnterior Estado anterior
     * @param string $novoEstado Novo estado
     * @return TarefaLog O log criado
     */
    public function registrarMudancaEstado(Tarefa $tarefa, string $estadoAnterior, string $novoEstado): TarefaLog
    {
        $descricao = "Estado alterado de '{$estadoAnterior}' para '{$novoEstado}'";
        
        $log = $this->registrarLog(
            $tarefa,
            'estado',
            'estado',
            $estadoAnterior,
            $novoEstado,
            $descricao
        );
        
        // Enviar notificação por email
        $this->enviarNotificacaoEmail($log, $tarefa);
        
        return $log;
    }

    /**
     * Registrar um log de exclusão de tarefa.
     *
     * @param Tarefa $tarefa A tarefa excluída
     * @return TarefaLog O log criado
     */
    public function registrarExclusao(Tarefa $tarefa): TarefaLog
    {
        $log = $this->registrarLog(
            $tarefa,
            'excluir',
            null,
            null,
            null,
            'Tarefa excluída'
        );
        
        // Enviar notificação por email
        $this->enviarNotificacaoEmail($log, $tarefa);
        
        return $log;
    }

    /**
     * Método base para registrar qualquer tipo de log.
     *
     * @param Tarefa $tarefa A tarefa
     * @param string $tipoAcao Tipo de ação (criar, atualizar, estado, excluir)
     * @param string|null $campoAlterado Campo alterado (para updates)
     * @param mixed $valorAnterior Valor anterior
     * @param mixed $valorNovo Novo valor
     * @param string $descricao Descrição da ação
     * @return TarefaLog O log criado
     */
    protected function registrarLog(
        Tarefa $tarefa,
        string $tipoAcao,
        ?string $campoAlterado,
        $valorAnterior,
        $valorNovo,
        string $descricao
    ): TarefaLog {
        return TarefaLog::create([
            'tarefa_id' => $tarefa->id,
            'utilizador_id' => Auth::id(),
            'tipo_acao' => $tipoAcao,
            'campo_alterado' => $campoAlterado,
            'valor_anterior' => is_array($valorAnterior) || is_object($valorAnterior) 
                ? json_encode($valorAnterior) 
                : (string) $valorAnterior,
            'valor_novo' => is_array($valorNovo) || is_object($valorNovo) 
                ? json_encode($valorNovo) 
                : (string) $valorNovo,
            'descricao' => $descricao,
        ]);
    }

    /**
     * Formatar a descrição da mudança para um campo específico.
     *
     * @param string $campo O campo que foi alterado
     * @param mixed $valorAnterior Valor anterior
     * @param mixed $valorNovo Novo valor
     * @return string A descrição formatada
     */
    protected function formatarDescricaoMudanca(string $campo, $valorAnterior, $valorNovo): string
    {
        $labelsCampos = [
            'titulo' => 'Título',
            'descricao' => 'Descrição',
            'estado' => 'Estado',
            'prioridade' => 'Prioridade',
            'categoria_id' => 'Categoria',
            'data_conclusao' => 'Data de conclusão',
        ];

        $nomeCampo = $labelsCampos[$campo] ?? $campo;

        // Formatar valores para exibição
        $valorAnteriorFormatado = $this->formatarValorParaExibicao($campo, $valorAnterior);
        $valorNovoFormatado = $this->formatarValorParaExibicao($campo, $valorNovo);

        if (empty($valorAnterior) && !empty($valorNovo)) {
            return "{$nomeCampo} definido como '{$valorNovoFormatado}'";
        } elseif (!empty($valorAnterior) && empty($valorNovo)) {
            return "{$nomeCampo} removido (era '{$valorAnteriorFormatado}')";
        } else {
            return "{$nomeCampo} alterado de '{$valorAnteriorFormatado}' para '{$valorNovoFormatado}'";
        }
    }

    /**
     * Formatar valores especiais para exibição.
     *
     * @param string $campo O campo
     * @param mixed $valor O valor
     * @return string O valor formatado
     */
    protected function formatarValorParaExibicao(string $campo, $valor): string
    {
        if ($valor === null) {
            return 'não definido';
        }

        // Formatar datas
        if ($campo === 'data_conclusao' && !empty($valor)) {
            return date('d/m/Y H:i', strtotime($valor));
        }

        return (string) $valor;
    }

    /**
     * Enviar notificação por email para o usuário dono da tarefa.
     *
     * @param TarefaLog $log O log da atividade
     * @param Tarefa $tarefa A tarefa relacionada
     * @return void
     */
    protected function enviarNotificacaoEmail(TarefaLog $log, Tarefa $tarefa): void
    {
        // Obter o usuário dono da tarefa
        $usuario = User::find($tarefa->utilizador_id);
        
        if ($usuario) {
            // Enviar a notificação imediatamente
            $notificacao = new TarefaAtividadeNotification($log, $tarefa);
            Notification::send($usuario, $notificacao);
        }
    }
} 