<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\JSONAPIRelationshipRequest;
use App\Models\Task;
use App\Models\User;
use App\Notifications\NotifyNewSupervisors;
use App\Notifications\NotifyRemovedSupervisors;
use App\Services\JSONAPIService;
use Illuminate\Support\Facades\Notification;

class TaskUsersRelationshipController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Task $task)
    {
        return $this->service->fetchRelationship($task, 'assignees');
    }

    /**
     * Assign users to a task
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(JSONAPIRelationshipRequest $request, Task $task)
    {

        return $this->service->updateManyToManyRelationships($task, 'assignees', $request->input('data.*.id'));
    }

    /**
     * Make or remove assigned users as supervisor
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function supervisor(JSONAPIRelationshipRequest $request, Task $task)
    {
        if (empty($request->input('data.*.id'))) {
            $task->assignees()->newPivotStatement()->where('task_id', '=', $task->id)
                ->update(array('is_supervisor' => 0));

            return response(null, 204);
        }

        $task->assignees()->syncWithPivotValues($request->input('data.*.id'), ['is_supervisor' => 1]);

        // $new_supervisors = User::whereIn('id', $request->input('data.*.id'))->get();
        // Notification::send($new_supervisors, new NotifyNewSupervisors(auth()->user()->name, $task));

        return response(null, 204);
    }
}
