<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\JSONAPIService;

class ProjectCreatorRelatedController extends Controller
{
    private $service;
    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }
    public function index(Project $project)
    {
        return $this->service->fetchRelated($project, 'creator');
    }
}
