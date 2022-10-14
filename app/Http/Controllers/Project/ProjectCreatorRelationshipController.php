<?php

namespace App\Http\Controllers\Project;

use App\Models\User;
use App\Models\Project;
use App\Services\JSONAPIService;
use App\Http\Controllers\Controller;
use App\Notifications\ProjectOwnerShipChange;
use App\Http\Requests\JSONAPIRelationshipRequest;

class ProjectCreatorRelationshipController extends Controller
{
    private $service;
    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Project $project)
    {
        return $this->service->fetchRelationship($project, 'creator');
    }

    public function update(JSONAPIRelationshipRequest $request, Project $project)
    {
        if ($request->user()->cannot('change_ownership', $project)) {
            abort(403, 'You are not the owner of this project');
        }
        $user = User::find($request->input('data.id'));

        if ($user) {
            $user->notify(new ProjectOwnerShipChange($project));
        }
        return $this->service->updateToOneRelationship($project, 'creator', $request->input('data.id'));
    }
}
