<?php

namespace App\Http\Controllers\Board;

use Illuminate\Http\Request;
use App\Services\JSONAPIService;
use App\Http\Controllers\Controller;
use App\Models\Board;

class BoardsTasksRelatedController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Board $board)
    {
        return $this->service->fetchRelated($board, 'tasks');
    }
}
