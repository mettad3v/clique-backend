<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GroupsTasksRelationshipsController;
use App\Http\Controllers\ProjectsUsersRelatedController;
use App\Http\Controllers\ProjectsUsersRelationshipController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
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
    Route::get('/user', function (Request $request) {
        return auth()->user();
    });
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::get('/users', [UserController::class, 'index'])->name('users.index');

    Route::apiResource('groups', GroupController::class);
    Route::get('/groups/{group}/relationships/tasks', [GroupsTasksRelationshipsController::class, 'index'])->name('groups.relationships.tasks');
    Route::patch('/groups/{group}/relationships/tasks', [GroupsTasksRelationshipsController::class, 'update'])->name('groups.relationships.tasks');
    Route::get('/groups/{group}/tasks', [GroupsTasksRelationshipsController::class, 'update'])->name('groups.tasks');
    
    
    Route::patch('/projects/{project}/change-ownership', [ProjectController::class, 'change_ownership']);
    Route::apiResource('projects', ProjectController::class);
    Route::patch('/projects/{project}/relationships/users', [ProjectsUsersRelationshipController::class, 'revoke']);
    Route::post('/projects/{project}/relationships/users', [ProjectsUsersRelationshipController::class, 'invite']);
    Route::get('/projects/{project}/relationships/users', [ProjectsUsersRelationshipController::class, 'index'])->name('projects.relationships.users');
    Route::patch('/projects/{project}/relationships/users', [ProjectsUsersRelationshipController::class, 'update'])->name('projects.relationships.users');
    Route::get('/projects/{project}/users', [ProjectsUsersRelatedController::class, 'index'])->name('projects.users');
    
    Route::apiResource('tasks', TaskController::class);
    Route::post('/tasks/{task}/assign', [TaskController::class, 'assign']);
    Route::patch('/tasks/{task}/supervisor', [TaskController::class, 'supervisor']);
    // Route::patch('/projects/{project}/revoke', [ProjectController::class, 'revoke']);

    Route::apiResource('categories', CategoryController::class);
});
