<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\JSONAPIResource;

class CurrentAuthenticatedUserController extends Controller
{
    public function show(Request $request)
    {
        return new JSONAPIResource($request->user());
    }
}
