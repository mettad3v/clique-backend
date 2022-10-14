<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\JSONAPIService;

class TaskProjectRelationshipController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Task $task)
    {
        return $this->service->fetchRelationship($task, 'project');
    }
}
