<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\Project;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class GroupsTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_returns_a_group_as_a_resource_object()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $group = Group::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/groups/1', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(200);
    }

    public function test_It_returns_all_groups_as_a_collection_of_resource_objects()
    {
        $project = Project::factory()->create();
        $groups = Group::factory(3)->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->get('/api/v1/groups', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(200);
    }

    public function test_It_can_paginate_groups_through_a_page_query_parameter()
    {
        $project = Project::factory()->create();
        $groups = Group::factory(10)->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->get('/api/v1/groups?page[size]=5&page[number]=1', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '1',
                    "type" => "groups",
                    "attributes" => [
                        'title' => $groups[0]->title,
                        'created_at' => $groups[0]->created_at->toJSON(),
                        'updated_at' => $groups[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "groups",
                    "attributes" => [
                        'title' => $groups[1]->title,
                        'created_at' => $groups[1]->created_at->toJSON(),
                        'updated_at' => $groups[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '3',
                    "type" => "groups",
                    "attributes" => [
                        'title' => $groups[2]->title,
                        'created_at' => $groups[2]->created_at->toJSON(),
                        'updated_at' => $groups[2]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '4',
                    "type" => "groups",
                    "attributes" => [
                        'title' => $groups[3]->title,
                        'created_at' => $groups[3]->created_at->toJSON(),
                        'updated_at' => $groups[3]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '5',
                    "type" => "groups",
                    "attributes" => [
                        'title' => $groups[4]->title,
                        'created_at' => $groups[4]->created_at->toJSON(),
                        'updated_at' => $groups[4]->updated_at->toJSON(),
                    ]
                ],
            ],
            'links' => [
                'first' => route('groups.index', ['page[size]' => 5, 'page[number]' => 1]),
                'last' => route('groups.index', ['page[size]' => 5, 'page[number]' => 2]),
                'prev' => null,
                'next' => route('groups.index', ['page[size]' => 5, 'page[number]' => 2]),
            ]
        ]);
    }

    public function it_can_sort_groups_by_name_through_a_sort_query_parameter()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $groups = collect([
            'Bertram',
            'Claus',
            'Anna',
        ])->map(function ($title) {
            return Group::factory()->create([
                'title' => $title,
            ]);
        });
        $this->get('/api/v1/groups?sort=title', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '3',
                    "type" => "groups",
                    "attributes" => [
                        'title' => 'Anna',
                        'created_at' => $groups[2]->created_at->toJSON(),
                        'updated_at' => $groups[2]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '1',
                    "type" => "groups",
                    "attributes" => [
                        'title' => 'Bertram',
                        'created_at' => $groups[0]->created_at->toJSON(),
                        'updated_at' => $groups[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "groups",
                    "attributes" => [
                        'title' => 'Claus',
                        'created_at' => $groups[1]->created_at->toJSON(),
                        'updated_at' => $groups[1]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }

    public function it_can_sort_groups_by_name_in_descending_order_through_a_sort_query_parameter()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $groups = collect([
            'Bertram',
            'Claus',
            'Anna',
        ])->map(function ($title) {
            return Group::factory()->create([
                'title' => $title,
            ]);
        });
        $this->get('/api/v1/groups?sort=-title', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '2',
                    "type" => "groups",
                    "attributes" => [
                        'title' => 'Claus',
                        'created_at' => $groups[1]->created_at->toJSON(),
                        'updated_at' => $groups[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '1',
                    "type" => "groups",
                    "attributes" => [
                        'title' => 'Bertram',
                        'created_at' => $groups[0]->created_at->toJSON(),
                        'updated_at' => $groups[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '3',
                    "type" => "groups",
                    "attributes" => [
                        'title' => 'Anna',
                        'created_at' => $groups[2]->created_at->toJSON(),
                        'updated_at' => $groups[2]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }

    public function test_it_can_sort_groups_by_multiple_sort_params_through_a_sort_query_parameter()

    {
        $project = Project::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $groups = collect([
            'Bertram',
            'Claus',
            'Anna',
        ])->map(function ($title) {
            if ($title === 'Bertram') {
                return Group::factory()->create([
                    'title' => $title,
                    'created_at' => now()->addSeconds(3),
                ]);
            }

            return Group::factory()->create([
                'title' => $title,
            ]);
        });
        $this->get('/api/v1/groups?sort=created_at,title', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '3',
                    "type" => "groups",
                    "attributes" => [
                        'title' => 'Anna',
                        'created_at' => $groups[2]->created_at->toJSON(),
                        'updated_at' => $groups[2]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "groups",
                    "attributes" => [
                        'title' => 'Claus',
                        'created_at' => $groups[1]->created_at->toJSON(),
                        'updated_at' => $groups[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '1',
                    "type" => "groups",
                    "attributes" => [
                        'title' => 'Bertram',
                        'created_at' => $groups[0]->created_at->toJSON(),
                        'updated_at' => $groups[0]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }

    public function test_it_can_sort_groups_by_multiple_sort_params_including_in_descending_order_through_a_sort_query_parameter()
    {
        $project = Project::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $groups = collect([
            'Bertram',
            'Claus',
            'Anna',
        ])->map(function ($title) {
            if ($title === 'Bertram') {
                return Group::factory()->create([
                    'title' => $title,
                    'created_at' => now()->addSeconds(3),
                ]);
            }

            return Group::factory()->create([
                'title' => $title,
            ]);
        });
        $this->get('/api/v1/groups?sort=-created_at,title', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '1',
                    "type" => "groups",
                    "attributes" => [
                        'title' => 'Bertram',
                        'created_at' => $groups[0]->created_at->toJSON(),
                        'updated_at' => $groups[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '3',
                    "type" => "groups",
                    "attributes" => [
                        'title' => 'Anna',
                        'created_at' => $groups[2]->created_at->toJSON(),
                        'updated_at' => $groups[2]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "groups",
                    "attributes" => [
                        'title' => 'Claus',
                        'created_at' => $groups[1]->created_at->toJSON(),
                        'updated_at' => $groups[1]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }


    public function test_it_can_create_a_group_from_a_resource_object()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        Sanctum::actingAs($user);

        // dd($group);
        $this->postJson('/api/v1/groups', [
            'data' => [
                'type' => 'groups',
                'attributes' => [
                    'title' => 'test'
                ],
                'relationships' => [
                    'project' => [
                        'data' => [
                            'id' => (string)$project->id,
                            'type' => 'projects'
                        ]
                    ],
                    'creator' => [
                        'data' => [
                            'id' => (string)$user->id,
                            'type' => 'users'
                        ]
                    ]
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(201)
            ->assertHeader('Location', url('/api/v1/groups/1'));

        $this->assertDatabaseHas('groups', [
            'id' => 1,
            'title' => 'test',
        ]);
    }

    public function test_it_validates_that_the_type_member_is_given_when_creating_a_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/groups', [
            'data' => [
                'type' => '',
                'attributes' => [
                    'title' => 'John Doe',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.type field is required.',
                    'source' => [
                        'pointer' => '/data/type',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseMissing('groups', [
            'id' => 1,
            'title' => 'John Doe'
        ]);
    }

    public function test_it_validates_that_the_type_member_is_given_when_updating_a_group()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $group = Group::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/groups/1', [
            'data' => [
                'id' => '1',
                'type' => '',
                'attributes' => [
                    'title' => 'John Doe',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.type field is required.',
                    'source' => [
                        'pointer' => '/data/type',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('groups', [
            'id' => 1,
            'title' => $group->title
        ]);
    }

    public function test_it_validates_that_the_type_member_has_the_value_of_groups_when_creating_a_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/groups', [
            'data' => [
                'type' => 'group',
                'attributes' => [
                    'title' => 'John Doe',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The selected data.type is invalid.',
                    'source' => [
                        'pointer' => '/data/type',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseMissing('groups', [
            'id' => 1,
            'title' => 'John Doe'
        ]);
    }

    public function test_it_validates_that_the_type_member_has_the_value_of_groups_when_updating_a_group()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $group = Group::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/groups/1', [
            'data' => [
                'id' => '1',
                'type' => 'group',
                'attributes' => [
                    'title' => 'John Doe',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The selected data.type is invalid.',
                    'source' => [
                        'pointer' => '/data/type',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('groups', [
            'id' => 1,
            'title' => $group->title
        ]);
    }

    public function test_it_validates_that_a_title_attribute_has_been_given_when_creating_a_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/groups', [
            'data' => [
                'type' => 'groups',
                'attributes' => [
                    'title' => '',
                ],

            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.title field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/title',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseMissing('groups', [
            'id' => 1,
            'title' => 'John Doe'
        ]);
    }

    public function test_it_validates_that_the_attributes_member_has_been_given_when_updating_a_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $project = Project::factory()->create();
        $group = Group::factory()->create();

        $this->patchJson('/api/v1/groups/1', [
            'data' => [
                'id' => '1',
                'type' => 'groups',

            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes field is required.',
                    'source' => [
                        'pointer' => '/data/attributes',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('groups', [
            'id' => 1,
            'title' => $group->title
        ]);
    }

    public function test_it_validates_that_a_title_attribute_is_a_string_when_creating_a_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/groups', [
            'data' => [
                'type' => 'groups',
                'attributes' => [
                    'title' => 47,
                ],

            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.title must be a string.',
                    'source' => [
                        'pointer' => '/data/attributes/title',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseMissing('groups', [
            'id' => 1,
            'title' => 'John Doe'
        ]);
    }

    public function test_it_validates_that_a_title_attribute_is_a_string_when_updating_a_group()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $group = Group::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/groups/1', [
            'data' => [
                'id' =>  '1',
                'type' => 'groups',
                'attributes' => [
                    'title' => 47,
                ],

            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.title must be a string.',
                    'source' => [
                        'pointer' => '/data/attributes/title',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('groups', [
            'id' => 1,
            'title' => $group->title
        ]);
    }

    public function test_it_validates_that_an_id_member_is_a_string_when_updating_a_group()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $group = Group::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/groups/1', [
            'data' => [
                'id' => 1,
                'type' => 'groups',
                'attributes' => [
                    'title' => 'Jane Doe',
                ]

            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.id must be a string.',
                    'source' => [
                        'pointer' => '/data/id',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('groups', [
            'id' => 1,
            'title' => $group->title
        ]);
    }
    public function test_it_validates_that_the_attributes_member_has_been_given_when_creating_a_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/groups', [
            'data' => [
                'type' => 'groups'
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes field is required.',
                    'source' => [
                        'pointer' => '/data/attributes',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseMissing('groups', [
            'id' => 1,
            'title' => 'John Doe'
        ]);
    }

    public function test_it_validates_that_the_attributes_member_is_an_object_given_when_creating_a_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/groups', [
            'data' => [
                'type' => 'groups',
                'attributes' => 'not an object'

            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes must be an array.',
                    'source' => [
                        'pointer' => '/data/attributes',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseMissing('groups', [
            'id' => 1,
            'title' => 'John Doe'
        ]);
    }

    public function test_it_validates_that_the_attributes_member_is_an_object_given_when_updating_a_group()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $group = Group::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/groups/1', [
            'data' => [
                'id' => '1',
                'type' => 'groups',
                'attributes' => 'not an object',

            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes must be an array.',
                    'source' => [
                        'pointer' => '/data/attributes',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('groups', [
            'id' => 1,
            'title' => $group->title
        ]);
    }

    public function test_it_can_update_an_group_from_a_resource_object()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $group = Group::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/groups/1', [
            'data' => [
                'id' => '1',
                'type' => 'groups',
                'attributes' => [
                    'title' => 'Jane Doe'
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ]);
        $this->assertDatabaseHas('groups', [
            'id' => 1,
            'title' => 'Jane Doe',

        ]);
    }

    public function test_it_validates_that_an_id_member_is_given_when_updating_a_group()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $group = Group::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/groups/1', [
            'data' => [
                'type' => 'groups',
                'attributes' => [
                    'title' => 'Jane Doe',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'Validation Error',
                        'details' => 'The data.id field is required.',
                        'source' => [
                            'pointer' => '/data/id',
                        ]
                    ]
                ]
            ]);
        $this->assertDatabaseHas('groups', [
            'id' => 1,
            'title' => $group->title,
        ]);
    }

    public function test_it_can_delete_a_group_through_a_delete_request()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $group = Group::factory()->create();
        Sanctum::actingAs($user);

        $this->delete('/api/v1/groups/1', [], [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ])->assertStatus(204);

        $this->assertDatabaseMissing('groups', [
            'id' => 1,
            'title' => $group->title,
        ]);
    }
}
