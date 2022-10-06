<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TasksTest extends TestCase
{
    use DatabaseMigrations;

    public function test_only_users_invited_to_the_parent_project_can_modify_task_resources()
    {
        $users = User::factory(2)->create();
        $auth = User::factory()->create();
        $project = Project::factory()->create();
        $project->invitees()->sync($users->pluck('id'));
        $uid =  Project::where('id', $project->id)->withCount('tasks')->get();
        $unique = $uid[0]->tasks_count + 1;
        $task = Task::factory()->create(['unique_id' => 'T-' . $unique]);
        Sanctum::actingAs($auth);

        dd($project);

        $this->patchJson('/api/v1/tasks/1', [
            'data' => [
                'id' => '1',
                'type' => 'tasks',
                'attributes' => [
                    'title' => 'John Doe',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(403);
    }

    public function test_it_returns_a_task_as_a_resource_object()
    {
        $project = Project::factory()->create();
        $uid =  Project::where('id', $project->id)->withCount('tasks')->get();
        $unique = $uid[0]->tasks_count + 1;
        $task = Task::factory()->create(['unique_id' => 'T-' . $unique]);
        $project->tasks()->save($task);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/tasks/1', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    "id" => '1',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => $task->title,
                        'deadline' => $task->deadline,
                        'unique_id' => $task->unique_id,
                        'project_id' => $project->id,
                        'description' => $task->description,
                        'created_at' => $task->created_at->toJSON(),
                        'updated_at' => $task->updated_at->toJSON(),
                    ]
                ]
            ]);
    }

    public function test_anyone_can_assign_tasks_to_other()
    {

        $auth = User::factory()->create();
        $project = Project::factory()->create();
        $user = User::factory(2)->create();
        $task = Task::factory()->create();

        Sanctum::actingAs($auth);
        $ids = $user->pluck('id');
        $project->invitees()->attach($ids);

        $this->patchJson('/api/v1/tasks/1/relationships/users', [
            'data' => [
                [
                    'id' => (string)$user[1]->id,
                    'type' => 'users'
                ],
                [
                    'id' => (string)$user[1]->id,
                    'type' => 'users'
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(204);
    }

    // public function test_it_returns_assignees_to_a_task()
    // {

    // }

    public function test_it_can_make_assigned_users_supervisor()
    {
        $auth = User::factory()->create();
        $project = Project::factory()->create();
        $user = User::factory(3)->create();
        $task = Task::factory()->create();

        Sanctum::actingAs($auth);
        $ids = $user->pluck('id');
        $project->invitees()->attach($ids);

        $task->assignees()->attach($ids);

        $this->patchJson('/api/v1/tasks/1/relationships/users/supervisor', [
            'data' => [
                [
                    'id' => '2',
                    'type' => 'users'
                ],
                [
                    'id' => '3',
                    'type' => 'users'
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(200);
    }

    public function test_It_returns_all_tasks_as_a_collection_of_resource_objects()
    {
        $tasks = Task::factory(3)->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->get('/api/v1/tasks', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(200);
    }

    public function test_It_can_paginate_tasks_through_a_page_query_parameter()
    {
        $tasks = Task::factory(10)->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->get('/api/v1/tasks?page[size]=5&page[number]=1', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '1',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => $tasks[0]->title,
                        'description' => $tasks[0]->description,
                        'created_at' => $tasks[0]->created_at->toJSON(),
                        'updated_at' => $tasks[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => $tasks[1]->title,
                        'description' => $tasks[1]->description,
                        'created_at' => $tasks[1]->created_at->toJSON(),
                        'updated_at' => $tasks[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '3',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => $tasks[2]->title,
                        'description' => $tasks[2]->description,
                        'created_at' => $tasks[2]->created_at->toJSON(),
                        'updated_at' => $tasks[2]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '4',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => $tasks[3]->title,
                        'description' => $tasks[3]->description,
                        'created_at' => $tasks[3]->created_at->toJSON(),
                        'updated_at' => $tasks[3]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '5',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => $tasks[4]->title,
                        'description' => $tasks[4]->description,
                        'created_at' => $tasks[4]->created_at->toJSON(),
                        'updated_at' => $tasks[4]->updated_at->toJSON(),
                    ]
                ],
            ],
            'links' => [
                'first' => route('tasks.index', ['page[size]' => 5, 'page[number]' => 1]),
                'last' => route('tasks.index', ['page[size]' => 5, 'page[number]' => 2]),
                'prev' => null,
                'next' => route('tasks.index', ['page[size]' => 5, 'page[number]' => 2]),
            ]
        ]);
    }

    public function test_it_can_sort_tasks_by_title_through_a_sort_query_parameter()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $tasks = collect([
            'Bertram',
            'Claus',
            'Anna',
        ])->map(function ($title) {
            return Task::factory()->create([
                'title' => $title,
            ]);
        });
        $this->get('/api/v1/tasks?sort=title', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '3',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => 'Anna',
                        'created_at' => $tasks[2]->created_at->toJSON(),
                        'updated_at' => $tasks[2]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '1',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => 'Bertram',
                        'created_at' => $tasks[0]->created_at->toJSON(),
                        'updated_at' => $tasks[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => 'Claus',
                        'created_at' => $tasks[1]->created_at->toJSON(),
                        'updated_at' => $tasks[1]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }

    public function test_it_can_sort_tasks_by_title_in_descending_order_through_a_sort_query_parameter()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $tasks = collect([
            'Bertram',
            'Claus',
            'Anna',
        ])->map(function ($title) {
            return Task::factory()->create([
                'title' => $title,
            ]);
        });
        $this->get('/api/v1/tasks?sort=-title', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '2',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => 'Claus',
                        'description' => $tasks[1]->description,
                        'created_at' => $tasks[1]->created_at->toJSON(),
                        'updated_at' => $tasks[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '1',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => 'Bertram',
                        'description' => $tasks[0]->description,
                        'created_at' => $tasks[0]->created_at->toJSON(),
                        'updated_at' => $tasks[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '3',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => 'Anna',
                        'description' => $tasks[2]->description,
                        'created_at' => $tasks[2]->created_at->toJSON(),
                        'updated_at' => $tasks[2]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }

    public function test_it_can_sort_tasks_by_multiple_sort_params_through_a_sort_query_parameter()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $tasks = collect([
            'Bertram',
            'Claus',
            'Anna',
        ])->map(function ($title) {
            if ($title === 'Bertram') {
                return Task::factory()->create([
                    'title' => $title,
                    'created_at' => now()->addSeconds(3),
                ]);
            }

            return Task::factory()->create([
                'title' => $title,
            ]);
        });
        $this->get('/api/v1/tasks?sort=created_at,title', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '3',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => 'Anna',
                        'created_at' => $tasks[2]->created_at->toJSON(),
                        'updated_at' => $tasks[2]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => 'Claus',
                        'created_at' => $tasks[1]->created_at->toJSON(),
                        'updated_at' => $tasks[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '1',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => 'Bertram',
                        'created_at' => $tasks[0]->created_at->toJSON(),
                        'updated_at' => $tasks[0]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }

    public function test_it_can_sort_tasks_by_multiple_sort_params_including_in_descending_order_through_a_sort_query_parameter()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $tasks = collect([
            'Bertram',
            'Claus',
            'Anna',
        ])->map(function ($title) {
            if ($title === 'Bertram') {
                return Task::factory()->create([
                    'title' => $title,
                    'created_at' => now()->addSeconds(3),
                ]);
            }

            return Task::factory()->create([
                'title' => $title,
            ]);
        });
        $this->get('/api/v1/tasks?sort=-created_at,title', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '1',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => 'Bertram',
                        'created_at' => $tasks[0]->created_at->toJSON(),
                        'updated_at' => $tasks[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '3',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => 'Anna',
                        'created_at' => $tasks[2]->created_at->toJSON(),
                        'updated_at' => $tasks[2]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "tasks",
                    "attributes" => [
                        'title' => 'Claus',
                        'created_at' => $tasks[1]->created_at->toJSON(),
                        'updated_at' => $tasks[1]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }


    public function test_it_can_create_a_task_from_a_resource_object()
    {
        // dd(Carbon::parse('2022-09-09 09:09:09')->diffForHumans());
        $project = Project::factory()->create();
        // $task = Task::factory()->create();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/tasks', [
            'data' => [
                'type' => 'tasks',
                'attributes' => [
                    'title' => 'John Doe',
                    'description' => 'John Doe and Jane Doe',
                    'deadline' => '2022-09-09'
                ],
                'relationships' => [
                    'project' => [
                        'data' => [
                            'id' => (string)$project->id,
                            'type' => 'projects'
                        ]
                    ]
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(201)
            ->assertHeader('Location', url('/api/v1/tasks/1'));

        $this->assertDatabaseHas('tasks', [
            'id' => 1,
            'title' => 'John Doe',
        ]);
    }

    public function test_it_validates_that_the_type_member_is_given_when_creating_a_task()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/tasks', [
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

        $this->assertDatabaseMissing('tasks', [
            'id' => 1,
            'title' => 'John Doe'
        ]);
    }

    public function test_it_validates_that_the_type_member_is_given_when_updating_a_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/tasks/1', [
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

        $this->assertDatabaseHas('tasks', [
            'id' => 1,
            'title' => $task->title
        ]);
    }

    public function test_it_validates_that_the_type_member_has_the_value_of_tasks_when_creating_a_task()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/tasks', [
            'data' => [
                'type' => 'task',
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

        $this->assertDatabaseMissing('tasks', [
            'id' => 1,
            'title' => 'John Doe'
        ]);
    }

    public function test_it_validates_that_the_type_member_has_the_value_of_tasks_when_updating_a_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/tasks/1', [
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

        $this->assertDatabaseHas('tasks', [
            'id' => 1,
            'title' => $task->title
        ]);
    }

    public function test_it_validates_that_a_title_attribute_has_been_given_when_creating_a_task()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/tasks', [
            'data' => [
                'type' => 'tasks',
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

        $this->assertDatabaseMissing('tasks', [
            'id' => 1,
            'title' => 'John Doe'
        ]);
    }

    public function test_it_validates_that_the_attributes_member_has_been_given_when_updating_a_task()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $task = Task::factory()->create();

        $this->patchJson('/api/v1/tasks/1', [
            'data' => [
                'id' => '1',
                'type' => 'tasks',

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

        $this->assertDatabaseHas('tasks', [
            'id' => 1,
            'title' => $task->title
        ]);
    }

    public function test_it_validates_that_a_title_attribute_is_a_string_when_creating_a_task()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/tasks', [
            'data' => [
                'type' => 'tasks',
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

        $this->assertDatabaseMissing('tasks', [
            'id' => 1,
            'title' => 'John Doe'
        ]);
    }

    public function test_it_validates_that_a_title_attribute_is_a_string_when_updating_a_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/tasks/1', [
            'data' => [
                'id' =>  '1',
                'type' => 'tasks',
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

        $this->assertDatabaseHas('tasks', [
            'id' => 1,
            'title' => $task->title
        ]);
    }

    public function test_it_validates_that_an_id_member_is_a_string_when_updating_a_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/tasks/1', [
            'data' => [
                'id' => 1,
                'type' => 'tasks',
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

        $this->assertDatabaseHas('tasks', [
            'id' => 1,
            'title' => $task->title
        ]);
    }

    public function test_it_validates_that_the_attributes_member_has_been_given_when_creating_a_task()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/tasks', [
            'data' => [
                'type' => 'tasks'
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

        $this->assertDatabaseMissing('tasks', [
            'id' => 1,
            'title' => 'John Doe'
        ]);
    }

    public function test_it_validates_that_the_attributes_member_is_an_object_given_when_creating_a_task()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/tasks', [
            'data' => [
                'type' => 'tasks',
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

        $this->assertDatabaseMissing('tasks', [
            'id' => 1,
            'title' => 'John Doe'
        ]);
    }

    public function test_it_validates_that_the_attributes_member_is_an_object_given_when_updating_a_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/tasks/1', [
            'data' => [
                'id' => '1',
                'type' => 'tasks',
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

        $this->assertDatabaseHas('tasks', [
            'id' => 1,
            'title' => $task->title
        ]);
    }

    public function test_it_can_update_a_task_from_a_resource_object()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/tasks/1', [
            'data' => [
                'id' => '1',
                'type' => 'tasks',
                'attributes' => [
                    'title' => 'Jane Doe',
                    'description' => 'another description',
                    'user_id' => 1
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => 1,
            'title' => 'Jane Doe',

        ]);
    }

    public function test_it_validates_that_an_id_member_is_given_when_updating_a_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/tasks/1', [
            'data' => [
                'type' => 'tasks',
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
        $this->assertDatabaseHas('tasks', [
            'id' => 1,
            'title' => $task->title,
        ]);
    }

    public function test_it_can_delete_a_task_through_a_delete_request()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        Sanctum::actingAs($user);

        $this->delete('/api/v1/tasks/1', [], [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ])->assertStatus(204);

        $this->assertDatabaseMissing('tasks', [
            'id' => 1,
            'title' => $task->title,
        ]);
    }
}
