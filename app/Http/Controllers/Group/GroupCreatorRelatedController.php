<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Services\JSONAPIService;

class GroupCreatorRelatedController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Group $group)
    {
        return $this->service->fetchRelated($group, 'creator');
    }
}
