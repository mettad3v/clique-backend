<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Group;
use Illuminate\Http\Request;
use App\Services\JSONAPIService;
use App\Http\Resources\GroupsIdentifierResource;
use App\Http\Requests\JSONAPIRelationshipRequest;
use Illuminate\Support\Facades\DB;

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
    // public function index(Group $group)
    // {
    //     return GroupsIdentifierResource::collection($group->tasks);
    // }

    public function update(JSONAPIRelationshipRequest $request, Group $group)
    {

        $x = Task::whereIn('id', $request->input('data.*.id'))->get();
        $group->tasks()->saveMany($x);

        return response(null, 204);
    }
}
