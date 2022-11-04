<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Task\TaskController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Board\BoardController;
use App\Http\Controllers\Group\GroupController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Task\TaskGroupRelatedController;
use App\Http\Controllers\Task\TaskUsersRelatedController;
use App\Http\Controllers\User\UsersTasksRelatedController;
use App\Http\Controllers\Task\TaskCreatorRelatedController;
use App\Http\Controllers\Task\TaskBoardRelatedController;
use App\Http\Controllers\Group\GroupCreatorRelatedController;
use App\Http\Controllers\User\UsersProjectsRelatedController;
use App\Http\Controllers\Task\TaskGroupRelationshipController;
use App\Http\Controllers\Task\TaskUsersRelationshipController;
use App\Http\Controllers\User\UsersTasksRelationshipController;
use App\Http\Controllers\Board\BoardsTasksRelatedController;
use App\Http\Controllers\Project\ProjectsUsersRelatedController;
use App\Http\Controllers\Task\TaskCreatorRelationshipController;
use App\Http\Controllers\Task\TaskBoardRelationshipController;
use App\Http\Controllers\User\UsersInvitationsRelatedController;
use App\Http\Controllers\Group\GroupsTasksRelationshipController;
use App\Http\Controllers\Project\ProjectCreatorRelatedController;
use App\Http\Controllers\Board\BoardCreatorRelatedController;
use App\Http\Controllers\User\CurrentAuthenticatedUserController;
use App\Http\Controllers\Group\GroupCreatorRelationshipController;
use App\Http\Controllers\User\UsersProjectsRelationshipController;
use App\Http\Controllers\User\UsersTasksAssignedRelatedController;
use App\Http\Controllers\Category\CategoriesTasksRelatedController;
use App\Http\Controllers\Board\BoardsTasksRelationshipController;
use App\Http\Controllers\Project\ProjectsUsersRelationshipController;
use App\Http\Controllers\User\UsersInvitationsRelationshipController;
use App\Http\Controllers\Project\ProjectCreatorRelationshipController;
use App\Http\Controllers\Board\BoardCreatorRelationshipController;
use App\Http\Controllers\Board\BoardProjectRelationshipController;
use App\Http\Controllers\Board\BoardProjectRelatedController;
use App\Http\Controllers\User\UsersTasksAssignedRelationshipController;
use App\Http\Controllers\Category\CategoriesTasksRelationshipController;

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

Route::post('/auth/register', [AuthController::class, 'register']);

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/users/current', [CurrentAuthenticatedUserController::class, 'show']);
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

    Route::get('/users/{user}/relationships/assigned-tasks', [UsersTasksAssignedRelationshipController::class, 'index'])->name('users.relationships.tasksAssigned');
    Route::patch('/users/{user}/relationships/assigned-tasks', [UsersTasksAssignedRelationshipController::class, 'update']);
    Route::get('/users/{user}/assigned-tasks', [UsersTasksAssignedRelatedController::class, 'index'])->name('users.tasksAssigned');

    Route::apiResource('groups', GroupController::class);
    Route::get('/groups/{group}/relationships/tasks', [GroupsTasksRelationshipController::class, 'index'])->name('groups.relationships.tasks');
    Route::patch('/groups/{group}/relationships/tasks', [GroupsTasksRelationshipController::class, 'update'])->name('groups.relationships.tasks');
    Route::get('/groups/{group}/tasks', [GroupsTasksRelationshipController::class, 'update'])->name('groups.tasks');
    Route::get('/groups/{group}/creator', [GroupCreatorRelatedController::class, 'index'])->name('groups.creator');
    Route::patch('/groups/{group}/relationships/creator', [GroupCreatorRelationshipController::class, 'update']);
    Route::get('/groups/{group}/relationships/creator', [GroupCreatorRelationshipController::class, 'index'])->name('groups.relationships.creator');

    Route::apiResource('projects', ProjectController::class);
    Route::get('/projects/{project}/relationships/invitees', [ProjectsUsersRelationshipController::class, 'index'])->name('projects.relationships.invitees');
    Route::patch('/projects/{project}/relationships/invitees', [ProjectsUsersRelationshipController::class, 'update']);
    Route::get('/projects/{project}/invitees', [ProjectsUsersRelatedController::class, 'index'])->name('projects.invitees');
    Route::patch('/projects/{project}/relationships/admin', [ProjectsUsersRelationshipController::class, 'admin']);

    // Route::patch('/projects/{project}/relationships/users/change-ownership', [ProjectCreatorRelationshipController::class, 'change_ownership']);
    Route::get('/projects/{project}/creator', [ProjectCreatorRelatedController::class, 'index'])->name('projects.creator');
    Route::patch('/projects/{project}/relationships/creator', [ProjectCreatorRelationshipController::class, 'update']);
    Route::get('/projects/{project}/relationships/creator', [ProjectCreatorRelationshipController::class, 'index'])->name('projects.relationships.creator');

    Route::apiResource('boards', BoardController::class);
    Route::get('/boards/{board}/tasks', [BoardsTasksRelatedController::class, 'index'])->name('boards.tasks');
    Route::get('/boards/{board}/relationships/tasks', [BoardsTasksRelationshipController::class, 'index'])->name('boards.relationships.tasks');

    Route::get('/boards/{board}/creator', [BoardCreatorRelatedController::class, 'index'])->name('boards.creator');
    Route::patch('/boards/{board}/relationships/creator', [BoardCreatorRelationshipController::class, 'update']);
    Route::get('/boards/{board}/relationships/creator', [BoardCreatorRelationshipController::class, 'index'])->name('boards.relationships.creator');

    Route::get('/boards/{board}/project', [BoardProjectRelatedController::class, 'index'])->name('boards.project');
    Route::patch('/boards/{board}/relationships/project', [BoardProjectRelationshipController::class, 'update']);
    Route::get('/boards/{board}/relationships/project', [BoardProjectRelationshipController::class, 'index'])->name('boards.relationships.project');
    Route::get('/boards/{board}/relationships/project', [BoardProjectRelationshipController::class, 'index'])->name('boards.relationships.project');

    Route::apiResource('tasks', TaskController::class);
    Route::get('/tasks/{task}/relationships/assignees', [TaskUsersRelationshipController::class, 'index'])->name('tasks.relationships.assignees');
    Route::patch('/tasks/{task}/relationships/assignees', [TaskUsersRelationshipController::class, 'update']);
    Route::get('/tasks/{task}/assignees', [TaskUsersRelatedController::class, 'index'])->name('tasks.assignees');

    Route::get('/tasks/{task}/relationships/board', [TaskBoardRelationshipController::class, 'index'])->name('tasks.relationships.board');
    Route::get('/tasks/{task}/board', [TaskBoardRelatedController::class, 'index'])->name('tasks.board');
    Route::patch('/tasks/{task}/relationships/supervisor', [TaskUsersRelationshipController::class, 'supervisor']);
    // Route::patch('/tasks/{task}/relationships/remove-supervisor', [TaskUsersRelationshipController::class, 'remove_supervisor']);

    Route::get('/tasks/{task}/creator', [TaskCreatorRelatedController::class, 'index'])->name('tasks.creator');
    Route::get('/tasks/{task}/relationships/creator', [TaskCreatorRelationshipController::class, 'index'])->name('tasks.relationships.creator');

    Route::get('/tasks/{task}/relationships/group', [TaskGroupRelationshipController::class, 'index'])->name('tasks.relationships.group');
    Route::get('/tasks/{task}/group', [TaskGroupRelatedController::class, 'index'])->name('tasks.group');

    Route::apiResource('categories', CategoryController::class);
    Route::get('/categories/{category}/tasks', [CategoriesTasksRelatedController::class, 'index'])->name('categories.tasks');
    Route::get('/categories/{category}/relationships/tasks', [CategoriesTasksRelationshipController::class, 'index'])->name('categories.relationships.tasks');
});
