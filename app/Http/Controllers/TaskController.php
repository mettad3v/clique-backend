<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Resources\TasksResource;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\TasksCollection;
use App\Notifications\NotifyAssignedUsers;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\Tasks\CreateTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Requests\Tasks\AssignUsersRequest;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tasks = QueryBuilder::for(Task::class)->allowedSorts([
            'title',
            'created_at',
            'updated_at'
        ])->jsonPaginate();
        return new TasksCollection($tasks);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateTaskRequest $request)
    {
        $project = Project::where('id', $request->input('data.attributes.project_id'))->withCount('tasks')->get();
        $unique_id = $project[0]->tasks_count + 1;

        $task = Task::create([
            'title' => $request->input('data.attributes.title'),
            'description' => $request->input('data.attributes.description'),
            'deadline' => $request->input('data.attributes.deadline'),
            'user_id' => $request->input('data.attributes.user_id'),
            'unique_id' => 'T-'.$unique_id,
            'project_id' => $request->input('data.attributes.project_id'),
        ]);
        return (new TasksResource($task))->response()->header('Location', route('tasks.show', ['task' => $task]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        return new TasksResource($task);

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
        Notification::send($assigned_users, new NotifyAssignedUsers($request->input('data.attributes.user_id'), $task));

        return response(null, 201);
        // return Carbon::parse('2022-04-13T18:35:25.000000Z')->diffForHumans();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->update($request->input('data.attributes'));
        return new TasksResource($task);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return response(null, 204);
    }
}
