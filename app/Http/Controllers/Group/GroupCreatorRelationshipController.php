<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\JSONAPIRelationshipRequest;
use App\Models\Group;
use App\Services\JSONAPIService;

class GroupCreatorRelationshipController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Group $group)
    {
        return $this->service->fetchRelationship($group, 'creator');
    }

    public function update(JSONAPIRelationshipRequest $request, Group $group)
    {
        return $this->service->updateToOneRelationship($group, 'creator', $request->input('data.id'));
    }
}
