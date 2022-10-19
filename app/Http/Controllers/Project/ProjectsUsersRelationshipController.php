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

        // $this->service->notificationHandler($request, $project, 'invitees', );
        return $this->service->updateManyToManyRelationships($project, 'invitees', $request->input('data.*.id'));
    }

    /**
     * Make or remove users as admin
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function admin(JSONAPIRelationshipRequest $request, Project $project)
    {
        if (Gate::denies('make_admin', $project)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        if (empty($request->input('data.*.id'))) {
            $project->invitees()->newPivotStatement()->where('project_id', '=', $project->id)
                ->update(array('is_admin' => 0));

            return response(null, 204);
        }

        $project->invitees()->syncWithPivotValues($request->input('data.*.id'), ['is_admin' => 1]);
        return response(null, 204);
    }
}
