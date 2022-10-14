<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Services\JSONAPIService;
use App\Http\Controllers\Controller;

class UsersInvitationsRelatedController extends Controller
{
    private $service;
    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }
    public function index(User $user)
    {
        return $this->service->fetchRelated($user, 'invitations');
    }
}
