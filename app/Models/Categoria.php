<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categoria extends Model
{
    use HasFactory;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'cor',
        'utilizador_id',
    ];

    /**
     * Obtém as tarefas associadas a esta categoria.
     */
    public function tarefas()
    {
        return $this->hasMany(Tarefa::class);
    }

    /**
     * Obtém o utilizador que criou a categoria.
     */
    public function utilizador()
    {
        return $this->belongsTo(User::class, 'utilizador_id');
    }
}
