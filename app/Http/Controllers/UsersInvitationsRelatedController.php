<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\JSONAPIService;

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
