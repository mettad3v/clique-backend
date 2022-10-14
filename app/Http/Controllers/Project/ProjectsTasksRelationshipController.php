<?php

namespace App\Http\Controller\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\JSONAPIService;

class ProjectsTasksRelationshipController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Project $project)
    {
        return $this->service->fetchRelationship($project, 'tasks');
    }
}
