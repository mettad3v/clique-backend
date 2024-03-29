<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\JSONAPIRequest;
use App\Http\Requests\Tasks\AssignUsersRequest;
use App\Models\Board;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\NotifyAssignedUsers;
use App\Notifications\NotifyNewSupervisors;
use App\Services\JSONAPIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

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

        $unique_id = Board::findOrFail((int) $request->input('data.relationships.board.data.id'))->tasks->count() + 1;

        return $this->service->createResource(Task::class, [
            'title' => $request->input('data.attributes.title'),
            'description' => $request->input('data.attributes.description'),
            'deadline' => $request->input('data.attributes.deadline'),
            'user_id' => auth()->user()->id,
            'unique_id' => 'T-' . (string) $unique_id,

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
        return $this->service->fetchResource(Task::class, $task, 'tasks');
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

        return $this->service->updateResource($task, $request->input('data.attributes'), $request->input('data.relationships'));
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

        return $this->service->deleteResource($task);
    }
}
