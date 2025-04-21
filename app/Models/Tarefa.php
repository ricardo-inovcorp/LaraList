<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarefa extends Model
{
    use HasFactory;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'utilizador_id',
        'titulo',
        'descricao',
        'estado',
        'prioridade',
        'categoria_id',
        'data_conclusao',
        'concluida',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_conclusao' => 'datetime',
        'concluida' => 'boolean',
    ];

    /**
     * Obter o utilizador a quem pertence esta tarefa.
     */
    public function utilizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilizador_id');
    }

    /**
     * Obter a categoria a que esta tarefa pertence.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
