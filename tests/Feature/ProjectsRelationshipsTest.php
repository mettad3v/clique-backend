<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ProjectsRelationshipsTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_returns_a_relationship_to_users_adhering_to_json_api_spec()
    {
        $auth = User::factory()->create();
        $users = User::factory(2)->create();
        $project = Project::factory()->create(['user_id' => $auth->id]);
        $project->invitees()->sync($users->pluck('id'));
        Sanctum::actingAs($auth);
        $this->getJson('/api/v1/projects/1?include=invitees', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => '1',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => $project->name
                    ],
                    'relationships' => [
                        'users' => [
                            'links' => [
                                'self' => route('projects.relationships.users', $project->id),
                                'related' => route('projects.users', $project->id),
                            ],
                            'data' => [
                                [
                                    'id' => (string)$users[0]->id,
                                    'type' => 'users'
                                ],
                                [
                                    'id' => (string)$users[1]->id,
                                    'type' => 'users'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_a_relationship_link_to_users_returns_all_related_users_as_resource_id_ob()
    {
        $auth = User::factory()->create();
        $users = User::factory(3)->create();
        $project = Project::factory()->create();
        $project->invitees()->attach($users->pluck('id'));
        Sanctum::actingAs($auth);
        $this->getJson('/api/v1/projects/1/relationships/users', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'id' => (string)$users[0]->id,
                        'type' => 'users',
                    ],
                    [
                        'id' => (string)$users[1]->id,
                        'type' => 'users',
                    ],
                    [
                        'id' => (string)$users[2]->id,
                        'type' => 'users',
                    ],
                ]
            ]);
    }

    public function test_project_creator_can_modify_relationships_to_users_and_add_new_relationships()
    {
        $users = User::factory(10)->create();
        $auth = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $auth->id]);
        $project->invitees()->attach($users->pluck('id'));
        Sanctum::actingAs($auth);
        $this->patchJson('/api/v1/projects/1/relationships/users', [
            'data' => [
                [
                    'id' => (string)$users[4]->id,
                    'type' => 'users',
                ],
                [
                    'id' => (string)$users[5]->id,
                    'type' => 'users',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);
        $this->assertDatabaseHas('project_user', [
            'user_id' => (string)$users[4]->id,
            'project_id' => 1,
        ])->assertDatabaseHas('project_user', [
            'user_id' => (string)$users[5]->id,
            'project_id' => 1,
        ]);
    }

    public function test_it_can_modify_relationships_to_users_and_remove_relationships()
    {
        $users = User::factory(10)->create();
        $auth = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $auth->id]);
        $project->invitees()->attach($users->pluck('id'));
        Sanctum::actingAs($auth);
        $this->patchJson('/api/v1/projects/1/relationships/users', [
            'data' => [
                [
                    'id' => (string)$users[0]->id,
                    'type' => 'users',
                ],
                [
                    'id' => (string)$users[1]->id,
                    'type' => 'users',
                ],
                [
                    'id' => (string)$users[2]->id,
                    'type' => 'users',
                ],
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);
        $this->assertDatabaseMissing('project_user', [
            'user_id' => (string)$users[3]->id,
            'project_id' => 1,
        ]);
    }

    public function test_it_can_remove_all_relationships_to_users_with_an_empty_collection()
    {
        $users = User::factory(10)->create();
        $auth = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $auth->id]);
        $project->invitees()->attach($users->pluck('id'));
        Sanctum::actingAs($auth);
        $this->patchJson('/api/v1/projects/1/relationships/users', [
            'data' => []
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);
        $this->assertDatabaseMissing('project_user', [
            'user_id' => 1,
            'project_id' => 1,
        ])->assertDatabaseMissing('project_user', [
            'user_id' => 2,
            'project_id' => 1,
        ])->assertDatabaseMissing('project_user', [
            'user_id' => 3,
            'project_id' => 1,
        ]);
    }

    public function test_it_returns_a_404_not_found_when_trying_to_add_relationship_to_a_non_existing_reference()
    {
        $users = User::factory(2)->create();
        $auth = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $auth->id]);
        $project->invitees()->saveMany($users);
        Sanctum::actingAs($auth);
        $this->patchJson('/api/v1/projects/1/relationships/users', [
            'data' => [
                [
                    'id' => '5',
                    'type' => 'users',
                ],
                [
                    'id' => '6',
                    'type' => 'users',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(404)->assertJson([
            'errors' => [
                [
                    'title' => 'Not Found Http Exception',
                    'details' => 'Given resource not found',
                ]
            ]
        ]);
    }

    public function test_it_validates_that_the_id_member_is_given_when_updating_a_relationship()
    {
        $users = User::factory(5)->create();
        $project = Project::factory()->create();
        $auth = User::factory()->create();
        Sanctum::actingAs($auth);
        $this->patchJson('/api/v1/projects/1/relationships/users', [
            'data' => [
                [
                    'type' => 'users',
                ],
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.0.id field is required.',
                    'source' => [
                        'pointer' => '/data/0/id',
                    ]
                ]
            ]
        ]);
    }

    public function test_it_validates_that_the_id_member_is_a_string_when_updating_a_relationship()
    {
        $users = User::factory(5)->create();
        $project = Project::factory()->create();
        $auth = User::factory()->create();
        Sanctum::actingAs($auth);
        $this->patchJson('/api/v1/projects/1/relationships/users', [
            'data' => [
                [
                    'id' => 5,
                    'type' => 'users',
                ],
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.0.id must be a string.',
                    'source' => [
                        'pointer' => '/data/0/id',
                    ]
                ]
            ]
        ]);
    }

    public function test_it_validates_that_the_type_member_is_given_when_updating_a_relationship()
    {
        $users = User::factory(5)->create();
        $project = Project::factory()->create();
        $auth = User::factory()->create();
        Sanctum::actingAs($auth);
        $this->patchJson('/api/v1/projects/1/relationships/users', [
            'data' => [
                [
                    'id' => '5',
                ],
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.0.type field is required.',
                    'source' => [
                        'pointer' => '/data/0/type',
                    ]
                ]
            ]
        ]);
    }

    public function it_validates_that_the_type_member_has_a_value_of_users_when_updating_a_r()
    {
        $users = User::factory(5)->create();
        $project = Project::factory()->create();
        $auth = User::factory()->create();
        Sanctum::actingAs($auth);
        $this->patchJson('/api/v1/projects/1/relationships/users', [
            'data' => [
                [
                    'id' => '5',
                    'type' => 'projects',
                ],
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The selected data.0.type is invalid.',
                    'source' => [
                        'pointer' => '/data/0/type',
                    ]
                ]
            ]
        ]);
    }

    public function test_it_can_get_all_related_users_as_resource_objects_from_related_link()
    {
        $auth = User::factory()->create();
        $users = User::factory(3)->create();
        $project = Project::factory()->create(['user_id' => $auth->id]);
        $project->invitees()->sync($users->pluck('id'));
        Sanctum::actingAs($auth);
        $this->getJson('/api/v1/projects/1/relationships/users', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200);
    }

    public function test_it_includes_related_resource_objects_when_an_include_query_param_is_given()
    {
        $project = Project::factory()->create();
        $users = User::factory(3)->create();
        $project->invitees()->sync($users->pluck('id'));
        $auth = User::factory()->create();
        Sanctum::actingAs($auth);
        $this->getJson('/api/v1/projects/1?include=invitees', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => '1',
                    'type' => 'projects',
                    'relationships' => [
                        'users' => [
                            'links' => [
                                'self' => route('projects.relationships.users', $project->id),
                                'related' => route('projects.users', $project->id),
                            ],
                            'data' => [
                                [
                                    'id' => (string)$users->get(0)->id,
                                    'type' => 'users'
                                ],
                                [
                                    'id' => (string)$users->get(1)->id,
                                    'type' => 'users'
                                ],
                                [
                                    'id' => (string)$users->get(2)->id,
                                    'type' => 'users'
                                ]
                            ]
                        ]
                    ]
                ],
                'included' => [
                    [
                        "id" => (string)$users->get(0)->id,
                        "type" => "users",
                        "attributes" => [
                            'name' => $users[0]->name,
                            'created_at' => $users[0]->created_at->toJSON(),
                            'updated_at' => $users[0]->updated_at->toJSON(),
                        ]
                    ],
                    [
                        "id" => (string)$users->get(1)->id,
                        "type" => "users",
                        "attributes" => [
                            'name' => $users[1]->name,
                            'created_at' => $users[1]->created_at->toJSON(),
                            'updated_at' => $users[1]->updated_at->toJSON(),
                        ]
                    ],
                    [
                        "id" => (string)$users->get(2)->id,
                        "type" => "users",
                        "attributes" => [
                            'name' => $users[2]->name,
                            'created_at' => $users[2]->created_at->toJSON(),
                            'updated_at' => $users[2]->updated_at->toJSON(),
                        ]
                    ],
                ]
            ]);
    }

    public function test_it_does_not_include_related_resource_objects_when_an_include_query_param_is_not_given()
    {
        $this->withoutExceptionHandling();
        $project = Project::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->getJson('/api/v1/projects/1', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
            ->assertStatus(200)
            ->assertJsonMissing([
                'included' => [],
            ]);
    }

    public function test_it_includes_related_resource_objects_for_a_collection_when_an_include_query_param_is_given()
    {
        $projects = Project::factory(3)->create();
        $users = User::factory(3)->create();

        $projects->each(function ($project, $key) use ($users) {
            if ($key === 0) {
                $project->invitees()->attach($users->pluck('id'));
            }
        });
        $auth = User::factory()->create();
        Sanctum::actingAs($auth);

        $this->get('/api/v1/projects?include=invitees', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '1',
                    "type" => "projects",
                    "attributes" => [
                        'name' => $projects[0]->name,
                        'created_at' => $projects[0]->created_at->toJSON(),
                        'updated_at' => $projects[0]->updated_at->toJSON(),
                    ],
                    'relationships' => [
                        'users' => [
                            'links' => [
                                'self' => route('projects.relationships.users', $projects[0]->id),
                                'related' => route('projects.users', $projects[0]->id),
                            ],
                            'data' => [
                                [
                                    'id' => (string)$users->get(0)->id,
                                    'type' => 'users'
                                ],
                                [
                                    'id' => (string)$users->get(1)->id,
                                    'type' => 'users'
                                ],
                                [
                                    'id' => (string)$users->get(2)->id,
                                    'type' => 'users'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "projects",
                    "attributes" => [
                        'name' => $projects[1]->name,
                        'created_at' => $projects[1]->created_at->toJSON(),
                        'updated_at' => $projects[1]->updated_at->toJSON(),
                    ],
                    'relationships' => [
                        'users' => [
                            'links' => [
                                'self' => route('projects.relationships.users', $projects[1]->id),
                                'related' => route('projects.users', $projects[1]->id),
                            ],
                        ]
                    ]
                ],
                [
                    "id" => '3',
                    "type" => "projects",
                    "attributes" => [
                        'name' => $projects[2]->name,
                        'created_at' => $projects[2]->created_at->toJSON(),
                        'updated_at' => $projects[2]->updated_at->toJSON(),
                    ],
                    'relationships' => [
                        'users' => [
                            'links' => [
                                'self' => route('projects.relationships.users', $projects[2]->id),
                                'related' => route('projects.users', $projects[2]->id),
                            ],
                        ]
                    ]
                ],
            ],
            'included' => [
                [
                    "id" => (string)$users->get(0)->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[0]->name,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => (string)$users->get(1)->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[1]->name,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => (string)$users->get(2)->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[2]->name,
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }

    public function test_it_does_not_include_related_resource_objects_for_a_collection_when_an_include_param_is_not_given()
    {
        $projects = Project::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->get('/api/v1/projects', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)
            ->assertJsonMissing([
                'included' => [],
            ]);
    }

    public function test_it_only_includes_a_related_resource_object_once_for_a_collection()
    {
        $users = User::factory(3)->create();
        $projects = Project::factory(3)->create();
        $projects->each(function ($project) use ($users) {
            $project->invitees()->attach($users->pluck('id'));
        });
        $auth = User::factory()->create();
        Sanctum::actingAs($auth);

        $this->get('/api/v1/projects?include=invitees', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '1',
                    "type" => "projects",
                    "attributes" => [
                        'name' => $projects[0]->name,
                        'created_at' => $projects[0]->created_at->toJSON(),
                        'updated_at' => $projects[0]->updated_at->toJSON(),
                    ],
                    'relationships' => [
                        'users' => [
                            'links' => [
                                'self' => route('projects.relationships.users', $projects[0]->id),
                                'related' => route('projects.users', $projects[0]->id),
                            ],
                            'data' => [
                                [
                                    'id' => $users->get(0)->id,
                                    'type' => 'users'
                                ],
                                [
                                    'id' => $users->get(1)->id,
                                    'type' => 'users'
                                ],
                                [
                                    'id' => $users->get(2)->id,
                                    'type' => 'users'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "projects",
                    "attributes" => [
                        'name' => $projects[1]->name,
                        'created_at' => $projects[1]->created_at->toJSON(),
                        'updated_at' => $projects[1]->updated_at->toJSON(),
                    ], 'relationships' => [
                        'users' => [
                            'links' => [
                                'self' => route('projects.relationships.users', $projects[1]->id),
                                'related' => route('projects.users', $projects[1]->id),
                            ],
                            'data' => [
                                [
                                    'id' => $users->get(0)->id,
                                    'type' => 'users'
                                ],
                                [
                                    'id' => $users->get(1)->id,
                                    'type' => 'users'
                                ],
                                [
                                    'id' => $users->get(2)->id,
                                    'type' => 'users'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    "id" => '3',
                    "type" => "projects",
                    "attributes" => [
                        'name' => $projects[2]->name,
                        'created_at' => $projects[2]->created_at->toJSON(),
                        'updated_at' => $projects[2]->updated_at->toJSON(),
                    ],
                    'relationships' => [
                        'users' => [
                            'links' => [
                                'self' => route('projects.relationships.users', $projects[2]->id),
                                'related' => route('projects.users', $projects[2]->id),
                            ],
                            'data' => [
                                [
                                    'id' => $users->get(0)->id,
                                    'type' => 'users'
                                ],
                                [
                                    'id' => $users->get(1)->id,
                                    'type' => 'users'
                                ],
                                [
                                    'id' => $users->get(2)->id,
                                    'type' => 'users'
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            'included' => [
                [
                    "id" => $users->get(0)->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[0]->name,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ]
                ], [
                    "id" => $users->get(1)->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[1]->name,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users->get(2)->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[2]->name,
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ]
                ],
            ]
        ])->assertJsonMissing([
            'included' => [
                [
                    "id" => $users->get(0)->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[0]->name,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users->get(1)->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[1]->name,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users->get(2)->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[2]->name,
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users->get(0)->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[0]->name,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users->get(1)->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[1]->name,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users->get(2)->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[2]->name, 'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users->get(0)->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[0]->name,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users->get(1)->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[1]->name,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users->get(2)->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[2]->name,
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }
}
