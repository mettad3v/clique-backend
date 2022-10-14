<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\JSONAPIRelationshipRequest;
use App\Models\Project;
use App\Services\JSONAPIService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

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
        if (Gate::denies('invite', $project)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        // $this->service->notificationHandler($request, $project, 'invitees', NotifyInvitedUsers::class, NotifyRevokedUsers::class, auth()->user());
        return $this->service->updateManyToManyRelationships($project, 'invitees', $request->input('data.*.id'));
    }
}
