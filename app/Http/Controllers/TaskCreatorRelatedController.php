<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Services\JSONAPIService;

class TaskCreatorRelatedController extends Controller
{
    private $service;
    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }
    public function index(Task $task)
    {
        return $this->service->fetchRelated($task, 'creator');
    }
}
