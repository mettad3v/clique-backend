<?php

namespace App\Http\Controllers;

use App\Http\Requests\JSONAPIRelationshipRequest;
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

    public function update(JSONAPIRelationshipRequest $request, Project $project)
    {
        if (!Gate::allows('invite', $project)) {
            abort(403);
        }

        // $this->service->notificationHandler($request, $project, 'invitees', NotifyInvitedUsers::class, NotifyRevokedUsers::class, auth()->user());

        $project->invitees()->sync($request->input('data.*.id'));
        return response(null, 204);
    }
}
