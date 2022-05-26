<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProjectsUsersRelatedController;
use App\Http\Controllers\UsersProjectsRelatedController;
use App\Http\Controllers\TaskUsersRelationshipController;
use App\Http\Controllers\UsersTasksRelationshipController;
use App\Http\Controllers\GroupsTasksRelationshipController;
use App\Http\Controllers\ProjectsUsersRelationshipController;
use App\Http\Controllers\UsersProjectsRelationshipController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/auth/register', [AuthController::class, 'register']);

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/users/current', function (Request $request) {
        return request()->user();
    });
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::apiResource('users', UserController::class);

    //routes for projects created by a user
    Route::get('/users/{user}/relationships/projects', [UsersProjectsRelationshipController::class, 'index'])->name('users.relationships.projects');
    // Route::patch('/users/{user}/relationships/projects', [UsersProjectsRelationshipController::class, 'update']);
    Route::get('/users/{user}/projects', [UsersProjectsRelatedController::class, 'index'])->name('users.projects');

    //routes for projects a user has been invited to
    Route::get('/users/{user}/relationships/invitations', [UsersInvitationsRelationshipController::class, 'index'])->name('users.relationships.invitations');
    Route::patch('/users/{user}/relationships/invitations', [UsersInvitationsRelationshipController::class, 'update']);
    Route::get('/users/{user}/invitations', [UsersInvitationsRelatedController::class, 'index'])->name('users.invitations');

    Route::get('/users/{user}/relationships/tasks', [UsersTasksRelationshipController::class, 'index'])->name('users.relationships.tasks');
    Route::patch('/users/{user}/relationships/tasks', [UsersTasksRelationshipController::class, 'update']);
    Route::get('/users/{user}/tasks', [UsersTasksRelatedController::class, 'index'])->name('users.tasks');

    Route::apiResource('groups', GroupController::class);
    Route::get('/groups/{group}/relationships/tasks', [GroupsTasksRelationshipController::class, 'index'])->name('groups.relationships.tasks');
    Route::patch('/groups/{group}/relationships/tasks', [GroupsTasksRelationshipController::class, 'update'])->name('groups.relationships.tasks');
    Route::get('/groups/{group}/tasks', [GroupsTasksRelationshipController::class, 'update'])->name('groups.tasks');
    
    
    Route::apiResource('projects', ProjectController::class);
    Route::get('/projects/{project}/relationships/users', [ProjectsUsersRelationshipController::class, 'index'])->name('projects.relationships.users');
    Route::patch('/projects/{project}/relationships/users', [ProjectsUsersRelationshipController::class, 'update']);
    Route::get('/projects/{project}/users', [ProjectsUsersRelatedController::class, 'index'])->name('projects.users');
    Route::patch('/projects/{project}/change-ownership', [ProjectController::class, 'change_ownership']);
    
    Route::get('/projects/{project}/creator', [ProjectCreatorRelatedController::class, 'index'])->name('projects.creator');
    Route::patch('/projects/{project}/relationships/creator', [ProjectCreatorRelationshipController::class, 'update']);
    Route::get('/projects/{project}/relationships/creator', [ProjectCreatorRelationshipController::class, 'index'])->name('projects.relationships.creator');
    
    Route::get('/projects/{project}/tasks', [ProjectsTasksRelatedController::class, 'index'])->name('projects.tasks');
    Route::get('/projects/{project}/relationships/tasks', [ProjectsTasksRelationshipController::class, 'index'])->name('projects.relationships.tasks');
    
    Route::apiResource('tasks', TaskController::class);
    Route::get('/tasks/{task}/relationships/users', [TaskUsersRelationshipController::class, 'index'])->name('tasks.relationships.users');
    Route::patch('/tasks/{task}/relationships/users', [TaskUsersRelationshipController::class, 'update']);
    Route::get('/tasks/{task}/users', [TaskUsersRelatedController::class, 'index'])->name('tasks.users');
    Route::patch('/tasks/{task}/relationships/users/supervisor', [TaskUsersRelationshipController::class, 'supervisor']);
    Route::patch('/tasks/{task}/relationships/users/remove-supervisor', [TaskUsersRelationshipController::class, 'remove_supervisor']);

    Route::apiResource('categories', CategoryController::class);
});
