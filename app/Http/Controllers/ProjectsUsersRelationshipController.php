<?php

namespace App\Http\Controllers;

use App\Http\Requests\JSONAPIRequest;
use App\Models\User;
use App\Models\Project;
use App\Services\JSONAPIService;
use Illuminate\Support\Facades\Gate;
use App\Notifications\NotifyInvitedUsers;
use App\Notifications\NotifyRevokedUsers;
use Illuminate\Support\Facades\Notification;

class ProjectsUsersRelationshipController extends Controller
{
    private $service;
    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Project $project)
    {
        return $this->service->fetchRelationship($project, 'invitees');

    }

    // public function update(ProjectsUsersRelationshipsRequest $request, Project $project)
    // {
    //     $ids = $request->input('data.*.id');
    //     $project->invitees()->sync($ids);
    //     return response(null, 204);
    // }

    public function invite(Project $project, JSONAPIRequest $request)
    {
        if (! Gate::allows('invite', $project)) {
            abort(403);
        }

        $project->invitees()->syncWithoutDetaching($request->input('data.*.id'));

        $attached_users = User::whereIn('id', auth()->user()->id)->get();
        Notification::send($attached_users, new NotifyInvitedUsers($project));

        return response(null, 201);
    }


    public function revoke(Project $project, JSONAPIRequest $request)
    {
        if (! Gate::allows('revoke', $project)) {
            abort(403);
        }

        $project->invitees()->detach($request->input('data.*.id'));

        $detached_users = User::whereIn('id', auth()->user()->id)->get();
        Notification::send($detached_users, new NotifyRevokedUsers($project));

        return response(null, 204);
    }
}
