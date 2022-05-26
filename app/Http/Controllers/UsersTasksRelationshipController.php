<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\JSONAPIService;
use App\Http\Requests\JSONAPIRelationshipRequest;

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
        return $this->service->fetchRelationship($user, 'tasksAssigned');
    }

    // public function update(JSONAPIRelationshipRequest $request, User $user)
    // {
        
    //     $user->tasksAssigned()->detach($request->input('data.*.id'));
    //     return response(null, 204);
    // }
}
