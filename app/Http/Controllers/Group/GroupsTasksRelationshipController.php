<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\JSONAPIRelationshipRequest;
use App\Models\Group;
use App\Models\Task;
use App\Services\JSONAPIService;

class GroupsTasksRelationshipController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Group $group)
    {
        return $this->service->fetchRelated($group, 'tasks');
    }

    public function update(JSONAPIRelationshipRequest $request, Group $group)
    {
        $x = Task::whereIn('id', $request->input('data.*.id'))->get();
        $group->tasks()->saveMany($x);

        return response(null, 204);
    }
}
