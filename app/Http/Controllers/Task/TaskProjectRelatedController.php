<?php

namespace App\Http\Controllers\Task;

use App\Models\Task;
use App\Services\JSONAPIService;
use App\Http\Controllers\Controller;

class TaskProjectRelatedController extends Controller
{
    private $service;
    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }
    public function index(Task $task)
    {
        return $this->service->fetchRelated($task, 'project');
    }
}
