<?php

namespace App\Http\Controllers\Board;

use Illuminate\Http\Request;
use App\Services\JSONAPIService;
use App\Http\Controllers\Controller;
use App\Http\Requests\JSONAPIRequest;
use App\Models\Board;

class BoardController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->service->fetchResources(Board::class, 'boards');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(JSONAPIRequest $request)
    {
        return $this->service->createResource(Board::class, [
            'title' => $request->input('data.attributes.title'),
            'user_id' => auth()->user()->id
        ], $request->input('data.relationships'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($board)
    {
        return $this->service->fetchResource(Board::class, $board, 'boards');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(JSONAPIRequest $request, Board $board)
    {
        // if ($request->user()->cannot('update', $board)) {
        //     abort(403, 'Access Denied');
        // }

        return $this->service->updateResource($board, $request->input('data.attributes'), $request->input('data.relationships'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Board $board)
    {
        // if ($request->user()->cannot('delete', $board)) {
        //     abort(403, 'You are not the owner of this board');
        // }

        // Notification::send($board->invitees, new boardDeleteNotification($board));

        return $this->service->deleteResource($board);
    }
}
