<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\JSONAPIService;
use App\Http\Requests\JSONAPIRequest;
use App\Http\Resources\JSONAPIResource;
use App\Notifications\NotifyAssignedUsers;
use App\Notifications\NotifyNewSupervisors;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\Tasks\AssignUsersRequest;

class TaskController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
        // $this->authorizeResource(Task::class, 'task');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->service->fetchResources(Task::class, 'tasks');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(JSONAPIRequest $request)
    {

        $unique_id = Project::findOrFail((int)$request->input('data.relationships.projects.data.id'))->tasks->count() + 1;

        return $this->service->createResource(Task::class, [
            'title' => $request->input('data.attributes.title'),
            'description' => $request->input('data.attributes.description'),
            'deadline' => $request->input('data.attributes.deadline'),
            'user_id' => auth()->user()->id,
            'unique_id' => 'T-' . (string)$unique_id,

        ], $request->input('data.relationships'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show($task)
    {
        // $x = ['a', 'b', 'c'];

        // foreach ($x as $key) {
        //     $key = 'x';
        // }
        // return $x;
        return $this->service->fetchResource(Task::class, $task, 'tasks');
    }

    /**
     * Assign users to a task
     * 
     * @param \App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function assign(AssignUsersRequest $request, Task $task)
    {

        $task->assignees()->syncWithoutDetaching($request->input('data.attributes.id'));

        $assigned_users = User::whereIn('id', $request->input('data.attributes.id'))->get();
        Notification::send($assigned_users, new NotifyAssignedUsers(auth()->user()->id, $task));

        return response(null, 200);
    }

    /**
     * Make assigned users supervisor
     * 
     * @param \App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function supervisor(AssignUsersRequest $request, Task $task)
    {

        $task->assignees()->updateExistingPivot($request->input('data.attributes.id'), [
            'is_supervisor' => 1
        ]);

        $new_supervisors = User::whereIn('id', $request->input('data.attributes.id'))->get();
        Notification::send($new_supervisors, new NotifyNewSupervisors($request->input('data.attributes.user_id'), $task));

        return response(null, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(JSONAPIRequest $request, Task $task)
    {
        if ($request->user()->cannot('update', $task)) {
            abort(403, 'Access Denied');
        }
        return $this->service->updateResource($task, $request->input('data.attributes'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task, Request $request)
    {
        if ($request->user()->cannot('delete', $task)) {
            abort(403, 'Access Denied');
        }
        return $task;
    }
}
