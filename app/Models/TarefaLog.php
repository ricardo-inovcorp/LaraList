<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarefaLog extends Model
{
    use HasFactory;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tarefa_id',
        'utilizador_id',
        'tipo_acao',
        'campo_alterado',
        'valor_anterior',
        'valor_novo',
        'descricao',
    ];

    /**
     * Obter a tarefa a que este log pertence.
     */
    public function tarefa(): BelongsTo
    {
        return $this->belongsTo(Tarefa::class);
    }

    /**
     * Obter o utilizador que realizou esta ação.
     */
    public function utilizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilizador_id');
    }
}
