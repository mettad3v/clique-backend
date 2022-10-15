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
        // return response(null, 204);
        // $this->service->notificationHandler($request, $task, 'assignees', NotifyAssignedUsers::class, NotifyUnassignedUsers::class, auth()->user());

        return $this->service->updateManyToManyRelationships($task, 'assignees', $request->input('data.*.id'));
    }

    /**
     * Make assigned users supervisor
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function supervisor(JSONAPIRelationshipRequest $request, Task $task)
    {

        $task->asignees()->syncWithPivotValues($request->input('data.*.id'), ['is_supervisor' => 1]);

        $new_supervisors = User::whereIn('id', $request->input('data.*.id'))->get();
        Notification::send($new_supervisors, new NotifyNewSupervisors(auth()->user(), $task));

        return response(null, 204);
    }

    // /**
    //  * Make assigned users supervisor
    //  *
    //  * @param  \App\Models\Task  $task
    //  * @return \Illuminate\Http\Response
    //  */
    // public function remove_supervisor(JSONAPIRelationshipRequest $request, Task $task)
    // {
    //     $task->assignees()->updateExistingPivot($request->input('data.*.id'), [
    //         'is_supervisor' => 0,
    //     ]);

    //     $new_supervisors = User::whereIn('id', $request->input('data.*.id'))->get();
    //     Notification::send($new_supervisors, new NotifyRemovedSupervisors(auth()->user(), $task));

    //     return response(null, 204);
    // }
}
