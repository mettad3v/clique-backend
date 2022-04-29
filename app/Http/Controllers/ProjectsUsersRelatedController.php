<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\JSONAPIService;
use App\Http\Resources\UsersCollection;
use App\Http\Resources\JSONAPICollection;

class ProjectsUsersRelatedController extends Controller
{
    private $service;
    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }
    public function index(Project $project)
    {
        return $this->service->fetchRelated($project, 'invitees');
    }
}
