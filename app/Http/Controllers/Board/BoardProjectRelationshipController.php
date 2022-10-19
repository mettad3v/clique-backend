<?php

namespace App\Http\Controllers\Board;

use App\Models\Board;
use App\Services\JSONAPIService;
use App\Http\Controllers\Controller;
use App\Http\Requests\JSONAPIRelationshipRequest;

class BoardProjectRelationshipController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Board $board)
    {
        return $this->service->fetchRelationship($board, 'project');
    }

    public function update(JSONAPIRelationshipRequest $request, Board $board)
    {
        // if ($request->user()->cannot('change_ownership', $board)) {
        //     abort(403, 'You are not the owner of this project');
        // }
        // $user = User::find($request->input('data.id'));

        // if ($user) {
        //     $user->notify(new ProjectOwnerShipChange($board));
        // }

        return $this->service->updateToOneRelationship($board, 'project', $request->input('data.id'));
    }
}
