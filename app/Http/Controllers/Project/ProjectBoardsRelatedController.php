<?php

namespace App\Http\Controllers\Project;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\JSONAPIService;
use App\Http\Controllers\Controller;

class ProjectBoardsRelatedController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Project $project)
    {
        return $this->service->fetchRelated($project, 'boards');
    }
}
