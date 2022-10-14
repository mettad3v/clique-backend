<?php

namespace App\Http\Controllers\Group;

use App\Models\Group;
use App\Services\JSONAPIService;
use App\Http\Controllers\Controller;
use App\Http\Requests\JSONAPIRequest;

class GroupController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return $this->service->fetchResources(Group::class, 'groups');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(JSONAPIRequest $request)
    {

        return $this->service->createResource(Group::class, [
            'title' => $request->input('data.attributes.title'),
            'user_id' => auth()->user()->id,
        ], $request->input('data.relationships'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function show($group)
    {
        return $this->service->fetchResource(Group::class, $group, 'groups');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(JSONAPIRequest $request, Group $group)
    {
        $this->service->updateResource($group, $request->input('data.attributes'), $request->input('data.relationships'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        return $this->service->deleteResource($group);
    }
}
