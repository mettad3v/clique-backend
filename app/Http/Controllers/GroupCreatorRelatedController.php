<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
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
