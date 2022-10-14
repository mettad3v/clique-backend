<?php

namespace App\Http\Controllers;

use App\Http\Requests\JSONAPIRequest;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\JSONAPIService;
use App\Http\Resources\JSONAPIResource;
use App\Notifications\NotifyInvitedUsers;
use App\Notifications\NotifyRevokedUsers;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProjectOwnerShipChange;
use App\Http\Requests\Projects\InviteUserRequest;
use App\Http\Requests\Projects\ChangeProjectOwnershipRequest;

class ProjectController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->service->fetchResources(Project::class, 'projects');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(JSONAPIRequest $request)
    {
        return $this->service->createResource(Project::class, [
            'name' => $request->input('data.attributes.name'),
            'user_id' => auth()->user()->id,
        ], $request->input('data.relationships'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show($project)
    {
        return $this->service->fetchResource(Project::class, $project, 'projects');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(JSONAPIRequest $request, Project $project)
    {
        if ($request->user()->cannot('update', $project)) {
            abort(403, 'Access Denied');
        }
        return $this->service->updateResource($project, $request->input('data.attributes'), $request->input('data.relationships'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Project $project)
    {
        if ($request->user()->cannot('delete', $project)) {
            abort(403, 'You are not the owner of this project');
        }

        // Notification::send($project->invitees, new ProjectDeleteNotification($project));

        return $this->service->deleteResource($project);
    }
}
