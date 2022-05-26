<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JSONAPIService;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\JSONAPIRequest;
use App\Http\Resources\JSONAPIResource;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
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
        return $this->service->fetchResources(User::class, 'users');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($user)
    {
        return $this->service->fetchResource(User::class, $user, 'users');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(JSONAPIRequest $request, User $user)
    {
        if ($request->hasFile('data.attributes.profile_avatar')) {

            Storage::delete($user->profile_avatar);
            $profile_avatar = $request->file('data.attributes.profile_avatar')->store('avatars');
        }

        return $this->service->updateResource($user, [
            'name' => $request->input('data.attributes.name'),
            'username' => $request->input('data.attributes.username'),
            'password' => bcrypt($request->input('data.attributes.password')),
            'email' => $request->input('data.attributes.email'),
            'status' => $request->input('data.attributes.status'),
            'profile_avatar' => $profile_avatar
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        return $this->service->deleteResource($user);
    }
}
