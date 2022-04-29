<?php

namespace App\Http\Controllers;

use App\Http\Requests\JSONAPIRequest;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\JSONAPIService;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\JSONAPIResource;
use App\Http\Resources\ProjectsResource;
use App\Http\Resources\JSONAPICollection;
use App\Notifications\NotifyInvitedUsers;
use App\Notifications\NotifyRevokedUsers;
use App\Http\Resources\ProjectsCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProjectOwnerShipChange;
use App\Notifications\ProjectDeleteNotification;
use App\Http\Requests\Projects\InviteUserRequest;
use App\Http\Requests\Projects\CreateProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
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
        $project = Project::create([
            'name' => $request->input('data.attributes.name'),
            'user_id' => auth()->user()->id,
        ]);
        return (new JSONAPIResource($project))->response()
                ->header('Location', route('projects.show', ['project' => $project]));

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
     * Project creator can invite other users.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function invite(Project $project, InviteUserRequest $request)
    {
        if ($request->user()->cannot('invite', $project)) {
            abort(403, 'You are not the owner of this project');
        }

        $project->invitees()->syncWithoutDetaching($request->input('data.attributes.id'));

        $attached_users = User::whereIn('id', $request->input('data.attributes.id'))->get();
        Notification::send($attached_users, new NotifyInvitedUsers($project));

        return response(null, 201);
    }

    public function revoke(Project $project, InviteUserRequest $request)
    {
        if ($request->user()->cannot('revoke', $project)) {
            abort(403, 'You are not the owner of this project');
        }

        $project->invitees()->detach($request->input('data.attributes.id'));

        $detached_users = User::whereIn('id', $request->input('data.attributes.id'))->get();
        Notification::send($detached_users, new NotifyRevokedUsers($project));

        return response(null, 204);
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
        $this->service->updateResource($project, $request->input('data.attributes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function change_ownership(ChangeProjectOwnershipRequest $request, Project $project)
    {
        if ($request->user()->cannot('change_ownership', $project)) {
            abort(403, 'You are not the owner of this project');
        }

        $project->update($request->input('data.attributes'));
        $user = User::find($request->input('data.attributes.user_id'));

        if ($user) {
            $user->notify(new ProjectOwnerShipChange($project));
        }
        return response(null, 204);
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
