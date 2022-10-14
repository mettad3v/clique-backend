<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use App\Services\JSONAPIService;
use App\Http\Requests\JSONAPIRelationshipRequest;

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
