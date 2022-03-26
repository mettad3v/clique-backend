<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\GroupsResource;
use App\Http\Resources\GroupsCollection;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Groups\Requests\CreateGroupRequest;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups = QueryBuilder::for(Group::class)->allowedSorts([
            'title',
            'created_at',
            'updated_at'
        ])->jsonPaginate();
        return new GroupsCollection($groups);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateGroupRequest $request)
    {
        $group = Group::create([
            'title' => $request->input('data.attributes.title'),
            // 'user_id' => $request->input('data.attributes.user_id'),
        ]);
        return (new GroupsResource($group))->response()->header('Location', route('groups.show', ['group' => $group]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function show(Group $group)
    {
        return new GroupsResource($group);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGroupRequest $request, Group $group)
    {
        $group->update($request->input('data.attributes'));
        return new GroupsResource($group);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        $group->delete();
        return response(null, 204);
    }
}
