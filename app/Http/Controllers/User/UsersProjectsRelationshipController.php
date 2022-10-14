<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Services\JSONAPIService;
use App\Http\Controllers\Controller;


class UsersProjectsRelationshipController extends Controller
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
        return $this->service->fetchRelationship($user, 'projects');
    }

    //you should be able to delete your projects and also change project ownership
    // public function update(JSONAPIRelationshipRequest $request, User $user)
    // {

    //     $user->projects()->detach($request->input('data.*.id'));
    //     return response(null, 204);
    // }
}
