<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\ProjectsResource;
use App\Http\Resources\ProjectsCollection;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\Projects\InviteUserRequest;
use App\Http\Requests\Projects\CreateProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Http\Requests\Projects\ChangeProjectOwnershipRequest;
use App\Notifications\NotifyInvitedUsers;
use App\Notifications\NotifyRevokedUsers;
use App\Notifications\ProjectDeleteNotification;
use App\Notifications\ProjectOwnerShipChange;
use Illuminate\Database\Eloquent\Collection;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projects = QueryBuilder::for(Project::class)->allowedSorts([
            'name',
            'created_at',
            'updated_at'
        ])->jsonPaginate();
        return new ProjectsCollection($projects);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateProjectRequest $request)
    {
        $project = Project::create([
            'name' => $request->input('data.attributes.name'),
            'user_id' => $request->input('data.attributes.user_id')
        ]);
        return (new ProjectsResource($project))->response()->header('Location', route('projects.show', ['project' => $project]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        return [Collect($project->invitees)];
        // return new ProjectsResource($project);
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

        $project->invitees()->attach($request->input('data.attributes.id'));

        if ($project->invitees) {
            Notification::send($project->invitees, new NotifyInvitedUsers($project));
        }
        return response(null, 201);
    }
    
    public function revoke(Project $project, InviteUserRequest $request)
    {
        if ($request->user()->cannot('revoke', $project)) {
            abort(403, 'You are not the owner of this project');
        }
        
        $project->invitees()->detach($request->input('data.attributes.id'));

        if ($project->invitees) {
            Notification::send($project->invitees, new NotifyRevokedUsers($project));
        }
        return response(null, 204);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->input('data.attributes'));
        return new ProjectsResource($project);
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
        
        $project->delete();
        return response(null, 204);
    }
}
