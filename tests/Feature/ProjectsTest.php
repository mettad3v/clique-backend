<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectsTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_returns_a_project_as_a_resource_object()
    {
        $project = Project::factory()->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/projects/1', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => '1',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => $project->name,
                        'created_at' => $project->created_at->toJSON(),
                        'updated_at' => $project->updated_at->toJSON(),
                    ],
                ],
            ]);
    }

    public function test_a_project_creator_can_invite_other_users_to_a_project()
    {
        $auth = User::factory()->create();
        $users = User::factory(3)->create();
        $project = Project::factory()->create(['user_id' => $auth->id]);

        Sanctum::actingAs($auth);

        $this->patchJson('/api/v1/projects/1/relationships/invitees', [
            'data' => [
                [
                    'id' => $users[0]->id,
                    'type' => 'users',
                ],
                [
                    'id' => $users[1]->id,
                    'type' => 'users',
                ],
                [
                    'id' => $users[2]->id,
                    'type' => 'users',
                ],
            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);
    }

    public function test_only_a_project_creator_can_delete_a_project()
    {
        $auth = User::factory()->create();
        $project = Project::factory()->create();

        Sanctum::actingAs($auth);

        $this->delete('/api/v1/projects/1', [], [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ])->assertStatus(403);
    }

    // public function test_a_project_creator_can_revoke_other_users_access_to_a_project()
    // {
    //     $auth = User::factory()->create();
    //     $user = User::factory(3)->create();
    //     $project = Project::factory()->create(['user_id' => $auth->id]);

    //     Sanctum::actingAs($auth);
    //     $ids = $user->pluck('id');

    //     $this->patchJson('/api/v1/projects/1/relationships/users', [
    //         'data' => [
    //             [
    //                 'id' => (string)$user[0]->id,
    //                 'type' => 'users'
    //             ],
    //             [
    //                 'id' => (string)$user[1]->id,
    //                 'type' => 'users'
    //             ],
    //             [
    //                 'id' => (string)$user[2]->id,
    //                 'type' => 'users'
    //             ]
    //         ]
    //     ], [
    //         'accept' => 'application/vnd.api+json',
    //         'content-type' => 'application/vnd.api+json'
    //     ])->assertStatus(204);
    // }

    public function test_a_project_creator_can_change_ownership_of_a_project()
    {
        $auth = User::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $auth->id]);

        Sanctum::actingAs($auth);

        $this->patchJson('/api/v1/projects/1/relationships/creator', [
            'data' => [
                [
                    'id' => (string) $auth->id,
                    'type' => 'users',
                ],
            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);
    }

    public function test_a_new_project_owner_is_notified_on_change_of_ownership_of_a_project()
    {
        $auth = User::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $auth->id]);

        Sanctum::actingAs($auth);

        $this->patchJson('/api/v1/projects/1/relationships/creator', [
            'data' => [
                [
                    'id' => (string) $auth->id,
                    'type' => 'users',
                ],
            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);

        Notification::fake();
        Notification::assertNotSentTo(
            [$user],
            ProjectOwnerShipChange::class
        );
    }

    public function test_It_returns_all_projects_as_a_collection_of_resource_objects()
    {
        $projects = Project::factory(3)->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->get('/api/v1/projects', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200);
    }

    public function test_It_can_paginate_projects_through_a_page_query_parameter()
    {
        $projects = Project::factory(10)->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->get('/api/v1/projects?page[size]=5&page[number]=1', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            'data' => [
                [
                    'id' => '1',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => $projects[0]->name,
                        'created_at' => $projects[0]->created_at->toJSON(),
                        'updated_at' => $projects[0]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '2',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => $projects[1]->name,
                        'created_at' => $projects[1]->created_at->toJSON(),
                        'updated_at' => $projects[1]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '3',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => $projects[2]->name,
                        'created_at' => $projects[2]->created_at->toJSON(),
                        'updated_at' => $projects[2]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '4',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => $projects[3]->name,
                        'created_at' => $projects[3]->created_at->toJSON(),
                        'updated_at' => $projects[3]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '5',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => $projects[4]->name,
                        'created_at' => $projects[4]->created_at->toJSON(),
                        'updated_at' => $projects[4]->updated_at->toJSON(),
                    ],
                ],
            ],
            'links' => [
                'first' => route('projects.index', ['page[size]' => 5, 'page[number]' => 1]),
                'last' => route('projects.index', ['page[size]' => 5, 'page[number]' => 2]),
                'prev' => null,
                'next' => route('projects.index', ['page[size]' => 5, 'page[number]' => 2]),
            ],
        ]);
    }

    public function it_can_sort_projects_by_name_through_a_sort_query_parameter()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $projects = collect([
            'Bertram',
            'Claus',
            'Anna',
        ])->map(function ($name) {
            return Project::factory()->create([
                'name' => $name,
            ]);
        });
        $this->get('/api/v1/projects?sort=name', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            'data' => [
                [
                    'id' => '3',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => 'Anna',
                        'created_at' => $projects[2]->created_at->toJSON(),
                        'updated_at' => $projects[2]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '1',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => 'Bertram',
                        'created_at' => $projects[0]->created_at->toJSON(),
                        'updated_at' => $projects[0]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '2',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => 'Claus',
                        'created_at' => $projects[1]->created_at->toJSON(),
                        'updated_at' => $projects[1]->updated_at->toJSON(),
                    ],
                ],
            ],
        ]);
    }

    public function it_can_sort_projects_by_name_in_descending_order_through_a_sort_query_parameter()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $projects = collect([
            'Bertram',
            'Claus',
            'Anna',
        ])->map(function ($name) {
            return Project::factory()->create([
                'name' => $name,
            ]);
        });
        $this->get('/api/v1/projects?sort=-name', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            'data' => [
                [
                    'id' => '2',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => 'Claus',
                        'created_at' => $projects[1]->created_at->toJSON(),
                        'updated_at' => $projects[1]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '1',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => 'Bertram',
                        'created_at' => $projects[0]->created_at->toJSON(),
                        'updated_at' => $projects[0]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '3',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => 'Anna',
                        'created_at' => $projects[2]->created_at->toJSON(),
                        'updated_at' => $projects[2]->updated_at->toJSON(),
                    ],
                ],
            ],
        ]);
    }

    public function test_it_can_sort_projects_by_multiple_sort_params_through_a_sort_query_parameter()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $projects = collect([
            'Bertram',
            'Claus',
            'Anna',
        ])->map(function ($name) {
            if ($name === 'Bertram') {
                return Project::factory()->create([
                    'name' => $name,
                    'created_at' => now()->addSeconds(3),
                ]);
            }

            return Project::factory()->create([
                'name' => $name,
            ]);
        });
        $this->get('/api/v1/projects?sort=created_at,name', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            'data' => [
                [
                    'id' => '3',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => 'Anna',
                        'created_at' => $projects[2]->created_at->toJSON(),
                        'updated_at' => $projects[2]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '2',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => 'Claus',
                        'created_at' => $projects[1]->created_at->toJSON(),
                        'updated_at' => $projects[1]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '1',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => 'Bertram',
                        'created_at' => $projects[0]->created_at->toJSON(),
                        'updated_at' => $projects[0]->updated_at->toJSON(),
                    ],
                ],
            ],
        ]);
    }

    public function test_it_can_sort_projects_by_multiple_sort_params_including_in_descending_order_through_a_sort_query_parameter()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $projects = collect([
            'Bertram',
            'Claus',
            'Anna',
        ])->map(function ($name) {
            if ($name === 'Bertram') {
                return Project::factory()->create([
                    'name' => $name,
                    'created_at' => now()->addSeconds(3),
                ]);
            }

            return Project::factory()->create([
                'name' => $name,
            ]);
        });
        $this->get('/api/v1/projects?sort=-created_at,name', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            'data' => [
                [
                    'id' => '1',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => 'Bertram',
                        'created_at' => $projects[0]->created_at->toJSON(),
                        'updated_at' => $projects[0]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '3',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => 'Anna',
                        'created_at' => $projects[2]->created_at->toJSON(),
                        'updated_at' => $projects[2]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '2',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => 'Claus',
                        'created_at' => $projects[1]->created_at->toJSON(),
                        'updated_at' => $projects[1]->updated_at->toJSON(),
                    ],
                ],
            ],
        ]);
    }

    public function test_it_can_create_a_project_from_a_resource_object()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/projects', [
            'data' => [
                'type' => 'projects',
                'attributes' => [
                    'name' => 'John Doe',
                ],
            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(201)
            ->assertJson([
                'data' => [
                    'id' => '1',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => 'John Doe',
                        'created_at' => now()->setMilliseconds(0)->toJSON(),
                        'updated_at' => now()->setMilliseconds(0)->toJSON(),
                    ],

                ],
            ])->assertHeader('Location', url('/api/v1/projects/1'));

        $this->assertDatabaseHas('projects', [
            'id' => 1,
            'name' => 'John Doe',
        ]);
    }

    public function test_it_validates_that_the_type_member_is_given_when_creating_a_project()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/projects', [
            'data' => [
                'type' => '',
                'attributes' => [
                    'name' => 'John Doe',
                ],
            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.type field is required.',
                    'source' => [
                        'pointer' => '/data/type',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseMissing('projects', [
            'id' => 1,
            'name' => 'John Doe',
        ]);
    }

    public function test_it_validates_that_the_type_member_is_given_when_updating_a_project()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/projects/1', [
            'data' => [
                'id' => '1',
                'type' => '',
                'attributes' => [
                    'name' => 'John Doe',
                ],
            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.type field is required.',
                    'source' => [
                        'pointer' => '/data/type',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => 1,
            'name' => $project->name,
        ]);
    }

    public function test_it_validates_that_the_type_member_has_the_value_of_projects_when_creating_a_project()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/projects', [
            'data' => [
                'type' => 'project',
                'attributes' => [
                    'name' => 'John Doe',
                ],
            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The selected data.type is invalid.',
                    'source' => [
                        'pointer' => '/data/type',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseMissing('projects', [
            'id' => 1,
            'name' => 'John Doe',
        ]);
    }

    public function test_it_validates_that_the_type_member_has_the_value_of_projects_when_updating_a_project()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/projects/1', [
            'data' => [
                'id' => '1',
                'type' => 'project',
                'attributes' => [
                    'name' => 'John Doe',
                ],
            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The selected data.type is invalid.',
                    'source' => [
                        'pointer' => '/data/type',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => 1,
            'name' => $project->name,
        ]);
    }

    public function test_it_validates_that_a_name_attribute_has_been_given_when_creating_a_project()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/projects', [
            'data' => [
                'type' => 'projects',
                'attributes' => [
                    'name' => '',
                ],

            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.name field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/name',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseMissing('projects', [
            'id' => 1,
            'name' => 'John Doe',
        ]);
    }

    public function test_it_validates_that_the_attributes_member_has_been_given_when_updating_a_project()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $project = Project::factory()->create();

        $this->patchJson('/api/v1/projects/1', [
            'data' => [
                'id' => '1',
                'type' => 'projects',

            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes field is required.',
                    'source' => [
                        'pointer' => '/data/attributes',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => 1,
            'name' => $project->name,
        ]);
    }

    public function test_it_validates_that_a_name_attribute_is_a_string_when_creating_a_project()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/projects', [
            'data' => [
                'type' => 'projects',
                'attributes' => [
                    'name' => 47,
                ],

            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.name must be a string.',
                    'source' => [
                        'pointer' => '/data/attributes/name',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseMissing('projects', [
            'id' => 1,
            'name' => 'John Doe',
        ]);
    }

    public function test_it_validates_that_a_name_attribute_is_a_string_when_updating_a_project()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/projects/1', [
            'data' => [
                'id' => '1',
                'type' => 'projects',
                'attributes' => [
                    'name' => 47,
                ],

            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.name must be a string.',
                    'source' => [
                        'pointer' => '/data/attributes/name',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => 1,
            'name' => $project->name,
        ]);
    }

    public function test_it_validates_that_an_id_member_is_a_string_when_updating_a_project()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/projects/1', [
            'data' => [
                'id' => 1,
                'type' => 'projects',
                'attributes' => [
                    'title' => 'Jane Doe',
                ],

            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.id must be a string.',
                    'source' => [
                        'pointer' => '/data/id',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => 1,
            'name' => $project->name,
        ]);
    }

    public function test_it_validates_that_the_attributes_member_has_been_given_when_creating_a_project()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/projects', [
            'data' => [
                'type' => 'projects',
            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes field is required.',
                    'source' => [
                        'pointer' => '/data/attributes',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseMissing('projects', [
            'id' => 1,
            'name' => 'John Doe',
        ]);
    }

    public function test_it_validates_that_the_attributes_member_is_an_object_given_when_creating_a_project()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/projects', [
            'data' => [
                'type' => 'projects',
                'attributes' => 'not an object',

            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes must be an array.',
                    'source' => [
                        'pointer' => '/data/attributes',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseMissing('projects', [
            'id' => 1,
            'name' => 'John Doe',
        ]);
    }

    public function test_it_validates_that_the_attributes_member_is_an_object_given_when_updating_a_project()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/projects/1', [
            'data' => [
                'id' => '1',
                'type' => 'projects',
                'attributes' => 'not an object',

            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes must be an array.',
                    'source' => [
                        'pointer' => '/data/attributes',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => 1,
            'name' => $project->name,
        ]);
    }

    public function test_it_can_update_a_project_from_a_resource_object()
    {
        $user = User::factory()->create();
        $project = Project::factory(['user_id' => $user->id])->create();
        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/projects/$project->id", [
            'data' => [
                'id' => '1',
                'type' => 'projects',
                'attributes' => [
                    'name' => 'Jane Doe',
                ],
            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => '1',
                    'type' => 'projects',
                    'attributes' => [
                        'name' => 'Jane Doe',
                        // 'created_at' => now()->setMilliseconds(0)->toJSON(),
                        // 'updated_at' => now()->setMilliseconds(0)->toJSON(),
                    ],
                ],
            ]);
        $this->assertDatabaseHas('projects', [
            'id' => 1,
            'name' => 'Jane Doe',

        ]);
    }

    public function test_it_validates_that_an_id_member_is_given_when_updating_a_project()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/projects/1', [
            'data' => [
                'type' => 'projects',
                'attributes' => [
                    'name' => 'Jane Doe',
                ],
            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'Validation Error',
                        'details' => 'The data.id field is required.',
                        'source' => [
                            'pointer' => '/data/id',
                        ],
                    ],
                ],
            ]);
        $this->assertDatabaseHas('projects', [
            'id' => 1,
            'name' => $project->name,
        ]);
    }

    public function test_it_can_delete_a_project_through_a_delete_request()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->delete('/api/v1/projects/1', [], [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ])->assertStatus(204);

        $this->assertDatabaseMissing('projects', [
            'id' => 1,
            'name' => $project->name,
        ]);
    }
}
