<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UsersResource;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\UsersCollection;
use Illuminate\Support\File;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Users\UpdateUserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = QueryBuilder::for(User::class)->allowedSorts([
            'name',
            'created_at',
            'updated_at'
        ])->jsonPaginate();
        return new UsersCollection($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateGroupRequest $request)
    {
        // $group = Group::create([
        //     'title' => $request->input('data.attributes.title'),
        //     // 'user_id' => $request->input('data.attributes.user_id'),
        // ]);
        // return (new GroupsResource($group))->response()->header('Location', route('groups.show', ['group' => $group]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return new UsersResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        if ($request->hasFile('data.attributes.profile_avatar')) {

            Storage::delete($user->profile_avatar);
            $profile_avatar = $request->file('data.attributes.profile_avatar')->store('avatars');

        }

        $user->update([
            'name' => $request->input('data.attributes.name'),
            'username' => $request->input('data.attributes.username'),
            'email' => $request->input('data.attributes.email'),
            'status' => $request->input('data.attributes.status'),
            'profile_avatar' => $profile_avatar 
        ]);
        return new UsersResource($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response(null, 204);
    }
}
