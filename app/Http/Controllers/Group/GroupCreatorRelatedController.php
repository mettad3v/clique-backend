<?php

namespace App\Http\Controllers\Group;

use App\Models\Group;
use App\Services\JSONAPIService;
use App\Http\Controllers\Controller;

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
