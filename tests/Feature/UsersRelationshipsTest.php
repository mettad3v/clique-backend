<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UsersRelationshipsTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_returns_a_relationship_to_projects_invited_to_adhering_to_json_api_spec()
    {
        $auth = User::factory()->create();
        $projects = Project::factory(2)->create();
        $auth->invitations()->sync($projects->pluck('id'));
        Sanctum::actingAs($auth);

        $this->getJson('/api/v1/users/'.$auth->id.'?include=invitations', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200);
    }
    
    public function test_it_returns_a_relationship_to_projects_owned_to_adhering_to_json_api_spec()
    {
        $auth = User::factory()->create();
        $projects = Project::factory(2)->create();
        $auth->projects()->saveMany($projects->pluck('id'));
        Sanctum::actingAs($auth);

        $this->getJson('/api/v1/users/'.$auth->id.'?include=projects', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200);
    }
    
    public function test_it_returns_a_relationship_to_tasks_adhering_to_json_api_spec()
    {
        $auth = User::factory()->create();
        $tasks = Task::factory(2)->create();
        $auth->tasksAssigned()->sync($tasks->pluck('id'));
        Sanctum::actingAs($auth);

        $this->getJson('/api/v1/users/'.$auth->id.'?include=tasksAssigned', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200);
    }

    public function test_a_relationship_link_to_projects_returns_all_related_projects_as_resource_id_ob()
    {
        $auth = User::factory()->create();
        $projects = Project::factory(3)->create();
        $auth->invitations()->sync($projects->pluck('id'));
        Sanctum::actingAs($auth);
        $this->getJson('/api/v1/users/'.$auth->id.'/relationships/projects', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'id' => '1',
                        'type' => 'projects',
                    ],
                    [
                        'id' => '2',
                        'type' => 'projects',
                    ],
                    [
                        'id' => '3',
                        'type' => 'projects',
                    ],
                ]
            ]);
    }

    public function test_project_user_can_remove_their_relationships_to_projects()
    {
        $auth = User::factory()->create();
        $projects = Project::factory(2)->create(['user_id' =>$auth->id]);
        $auth->invitations()->attach($projects->pluck('id'));
        Sanctum::actingAs($auth);
        $this->patchJson('/api/v1/users/'.$auth->id.'/relationships/projects', [
            'data' => [
                [
                    'id' => '2',
                    'type' => 'projects',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);
        $this->assertDatabaseMissing('project_user', [
            'user_id' => 1,
            'project_id' => 5,
        ]);
    }

    public function test_it_can_get_all_related_projects_as_resource_objects_from_related_link()
    {
        $auth = User::factory()->create();
        $projects = Project::factory(3)->create();
        $auth->invitations()->attach($projects->pluck('id'));

        Sanctum::actingAs($auth);
        $this->getJson('/api/v1/users/'.$auth->id.'/projects', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200);
    }

    public function test_it_includes_related_resource_objects_when_an_include_query_param_is_given()
    {
        $auth = User::factory()->create();
        $projects = Project::factory(3)->create();
        $auth->invitations()->sync($projects->pluck('id'));
        Sanctum::actingAs($auth);
        $this->getJson('/api/v1/users/'.$auth->id.'?include=invitations', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $auth->id,
                    'type' => 'users',
                    'relationships' => [
                        'projects' => [
                            'links' => [
                                'self' => route('users.relationships.projects', $auth->id),
                                'related' => route('users.projects', $auth->id),
                            ],
                            'data' => [
                                [
                                    'id' => (string)$projects->get(0)->id,
                                    'type' => 'projects'
                                ],
                                [
                                    'id' => (string)$projects->get(1)->id,
                                    'type' => 'projects'
                                ],
                                [
                                    'id' => (string)$projects->get(2)->id,
                                    'type' => 'projects'
                                ]
                            ]
                        ]
                    ]
                ],
                'included' => [
                    [
                        "id" => '1',
                        "type" => "projects",
                        "attributes" => [
                            'name' => $projects[0]->name,
                            'created_at' => $projects[0]->created_at->toJSON(),
                            'updated_at' => $projects[0]->updated_at->toJSON(),
                        ]
                    ],
                    [
                        "id" => '2',
                        "type" => "projects",
                        "attributes" => [
                            'name' => $projects[1]->name,
                            'created_at' => $projects[1]->created_at->toJSON(),
                            'updated_at' => $projects[1]->updated_at->toJSON(),
                        ]
                    ],
                    [
                        "id" => '3',
                        "type" => "projects",
                        "attributes" => [
                            'name' => $projects[2]->name,
                            'created_at' => $projects[2]->created_at->toJSON(),
                            'updated_at' => $projects[2]->updated_at->toJSON(),
                        ]
                    ],
                ]
            ]);
    }

    
}
