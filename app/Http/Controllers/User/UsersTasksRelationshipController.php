<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\JSONAPIRelationshipRequest;
use App\Models\User;
use App\Services\JSONAPIService;

class UsersTasksRelationshipController extends Controller
{
    /**
     * var JSONAPIService
     */
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(User $user)
    {
        return $this->service->fetchRelationship($user, 'tasks');
    }

    public function update(JSONAPIRelationshipRequest $request, User $user)
    {
        $user->tasks()->detach($request->input('data.*.id'));

        return response(null, 204);
    }
}
