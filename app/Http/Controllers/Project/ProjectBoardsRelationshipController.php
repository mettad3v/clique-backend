<?php

namespace App\Http\Controllers\Project;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\JSONAPIService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\JSONAPIRelationshipRequest;
use Illuminate\Auth\Access\AuthorizationException;

class ProjectBoardsRelationshipController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Project $project)
    {
        return $this->service->fetchRelationship($project, 'boards');
    }

    public function update(JSONAPIRelationshipRequest $request, Project $project)
    {
        if (Gate::denies('invite', $project)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        // $this->service->notificationHandler($request, $project, 'invitees', );
        return $this->service->updateManyToManyRelationships($project, 'boards', $request->input('data.*.id'));
    }
}
