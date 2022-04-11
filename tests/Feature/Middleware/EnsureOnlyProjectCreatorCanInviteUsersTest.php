<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Http\Middleware\EnsureProjectCreatorCanInvite;

class EnsureOnlyProjectCreatorCanInviteUsersTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * Only project creator can invite users.
     *
     * @return void
     */
    public function test_aborts_request_if_user_is_not_project_creator()
    {
        $user = User::factory()->create();
        $project = Project::factory()->make(['user_id' => 2]);
        Sanctum::actingAs($user);

        $request = Request::create('/api/v1/projects/1/invite', 'POST');
        $request->headers->set('accept', 'application/vnd.api+json');
        $request->headers->set('content-type', 'application/vnd.api+json');
        
        dd($request->getRequestUri());
        $middleware = new EnsureProjectCreatorCanInvite;

        /** @var Response $response */
        $response = $middleware->handle($request, function ($request) {
            return new Response();
        });

        $this->assertEquals(403, $response->status());
        
    }
    
    public function test_doesnt_abort_request_if_user_is_project_creator()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $request = Request::create('/api/v1/projects/1/invite', 'POST');
        $request->headers->set('accept', 'application/vnd.api+json');
        $request->headers->set('content-type', 'application/vnd.api+json');
        
        $middleware = new EnsureProjectCreatorCanInvite;

        /** @var Response $response */
        $response = $middleware->handle($request, function ($request) {
            return new Response();
        });

        $this->assertEquals($response, null);
    }
}
