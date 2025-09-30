<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TareaComentario;
use Illuminate\Auth\Access\Response;

class TareaComentarioPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TareaComentario $tareaComentario): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TareaComentario $tareaComentario): bool
    {
        return false;
    }

    /**
     * Solo el autor del comentario puede eliminarlo.
     */
    public function delete(User $user, TareaComentario $comentario): bool
    {
        return (int)$user->id === (int)$comentario->usuario_id;
    }
    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TareaComentario $tareaComentario): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TareaComentario $tareaComentario): bool
    {
        return false;
    }
}