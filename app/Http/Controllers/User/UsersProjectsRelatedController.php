<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JSONAPIService;

class UsersProjectsRelatedController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(User $user)
    {
        return $this->service->fetchRelated($user, 'projects');
    }
}
