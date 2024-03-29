<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can invite other users.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function invite(User $user, Project $project)
    {
        $user_invited = Project::find($project->id)->invitees()
            ->where('user_id', $user->id)
            ->first();

        return $user->id === $project->user_id | $user_invited?->pivot->is_admin;
    }

    /**
     * Determine whether the user can revoke other users.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function revoke(User $user, Project $project)
    {
        $user_invited = Project::find($project->id)->invitees()
            ->where('user_id', $user->id)
            ->first();

        if (!$user_invited) {
            return false;
        }

        return $user->id === $project->user_id | $user_invited->pivot->is_admin;
    }

    /**
     * Determine whether the user can revoke other users.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function make_admin(User $user, Project $project)
    {
        return $user->id === $project->user_id
            ? Response::allow()
            : Response::deny('You do not own this project.');
    }

    /**
     * Determine whether the user can make invitees admin.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function change_ownership(User $user, Project $project)
    {
        return $user->id === $project->user_id
            ? Response::allow()
            : Response::deny('You do not own this project.');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Project $project)
    {
        // $project = $project->project;
        $result = $project->invitees()->where('user_id', $user->id)->get();

        return $project->user_id === $user->id || $result->isNotEmpty() ? true : false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Project $project)
    {
        return $project->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Project $project)
    {
        return $user->id === $project->user_id
            ? Response::allow()
            : Response::deny('You do not own this project.');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Project $project)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Project $project)
    {
    }
}
