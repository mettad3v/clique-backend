<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Services\JSONAPIService;
use App\Http\Requests\JSONAPIRequest;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\JSONAPIResource;
use App\Http\Resources\JSONAPICollection;

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
            'project_id' => $request->input('data.attributes.project_id'),
        ]);
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
        $this->service->updateResource($group, $request->input('data.attributes'));
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
