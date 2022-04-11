<?php

namespace App\Http\Controllers;

use App\Http\Resources\GroupsIdentifierResource;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupsTasksRelationshipsController extends Controller
{
    public function index(Group $group)
    {
        return GroupsIdentifierResource::collection($group->tasks);
    }

    public function update(Request $request, Group $group)
    {
        // $ids = $request->input('data.*.id');    
        // $group->tasks()->whereNotIn('id', $ids)->update();
        if ($group->tasks->empty) {
            dd($group->tasks);
        }else{
            dd(0);
        }
        return response(null, 204);

    }
}
