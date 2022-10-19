<?php

namespace App\Http\Controllers\Board;

use App\Models\Board;
use Illuminate\Http\Request;
use App\Services\JSONAPIService;
use App\Http\Controllers\Controller;

class BoardCreatorRelatedController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Board $board)
    {
        return $this->service->fetchRelated($board, 'creator');
    }
}
