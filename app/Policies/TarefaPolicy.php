<?php

namespace App\Policies;

use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TarefaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Qualquer utilizador autenticado pode ver suas próprias tarefas
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tarefa $tarefa): bool
    {
        return $user->id === $tarefa->utilizador_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Qualquer utilizador autenticado pode criar tarefas
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tarefa $tarefa): bool
    {
        return $user->id === $tarefa->utilizador_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tarefa $tarefa): bool
    {
        return $user->id === $tarefa->utilizador_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tarefa $tarefa): bool
    {
        return $user->id === $tarefa->utilizador_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tarefa $tarefa): bool
    {
        return $user->id === $tarefa->utilizador_id;
    }
}
