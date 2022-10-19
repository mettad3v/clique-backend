<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TasksRelationshipsTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_returns_a_relationship_to_users_adhering_to_json_api_spec()
    {
        $auth = User::factory()->create();
        $users = User::factory(2)->create();
        $project = Project::factory()->create(['user_id' => $auth->id]);
        $task = Task::factory()->create();
        $task->assignees()->sync($users->pluck('id'));
        Sanctum::actingAs($auth);
        $this->getJson('/api/v1/tasks/1?include=assignees', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => '1',
                    'type' => 'tasks',
                    'attributes' => [
                        'title' => $task->title,

                    ],
                    'relationships' => [
                        'assignees' => [
                            'links' => [
                                'self' => route('tasks.relationships.assignees', $task->id),
                                'related' => route('tasks.assignees', $task->id),
                            ],
                            'data' => [
                                [
                                    'id' => $users[0]->id,
                                    'type' => 'users',
                                ],
                                [
                                    'id' => $users[1]->id,
                                    'type' => 'users',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_a_relationship_link_to_users_returns_all_related_users_as_resource_id_ob()
    {
        $auth = User::factory()->create();
        $users = User::factory(3)->create();
        $project = Project::factory()->create(['user_id' => $auth->id]);
        $task = Task::factory()->create();
        $task->assignees()->sync($users->pluck('id'));
        Sanctum::actingAs($auth);
        $this->getJson('/api/v1/tasks/1/relationships/assignees', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
            ->assertStatus(200)
            ->assertJson([
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
            ]);
    }

    public function test_task_user_can_modify_relationships_to_users_and_add_new_relationships()
    {
        $users = User::factory(10)->create();
        $auth = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $auth->id]);
        $task = Task::factory()->create();
        $task->assignees()->sync($users->pluck('id'));
        Sanctum::actingAs($auth);
        $this->patchJson('/api/v1/tasks/1/relationships/assignees', [
            'data' => [
                [
                    'id' => (string) $users[4]->id,
                    'type' => 'users',
                ],
                [
                    'id' => (string) $users[5]->id,
                    'type' => 'users',
                ],
            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);
        $this->assertDatabaseHas('task_user', [
            'user_id' => (string) $users[4]->id,
            'task_id' => 1,
        ])->assertDatabaseHas('task_user', [
            'user_id' => (string) $users[5]->id,
            'task_id' => 1,
        ]);
    }

    public function test_it_can_modify_relationships_to_users_and_remove_relationships()
    {
        $users = User::factory(10)->create();
        $auth = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $auth->id]);
        $task = Task::factory()->create();
        $task->assignees()->sync($users->pluck('id'));
        Sanctum::actingAs($auth);
        $this->patchJson('/api/v1/tasks/1/relationships/assignees', [
            'data' => [
                [
                    'id' => (string) $users[0]->id,
                    'type' => 'users',
                ],
                [
                    'id' => (string) $users[1]->id,
                    'type' => 'users',
                ],
                [
                    'id' => (string) $users[4]->id,
                    'type' => 'users',
                ],
            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);
        $this->assertDatabaseHas('task_user', [
            'user_id' => (string) $users[0]->id,
            'task_id' => 1,
        ])->assertDatabaseHas('task_user', [
            'user_id' => (string) $users[1]->id,
            'task_id' => 1,
        ])->assertDatabaseHas('task_user', [
            'user_id' => (string) $users[4]->id,
            'task_id' => 1,
        ])->assertDatabaseMissing('task_user', [
            'user_id' => (string) $users[2]->id,
            'task_id' => 1,
        ])->assertDatabaseMissing('task_user', [
            'user_id' => (string) $users[3]->id,
            'task_id' => 1,
        ]);
    }

    public function test_it_can_remove_all_relationships_to_users_with_an_empty_collection()
    {
        $users = User::factory(10)->create();
        $auth = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $auth->id]);
        $task = Task::factory()->create();
        $task->assignees()->sync($users->pluck('id'));
        Sanctum::actingAs($auth);
        $this->patchJson('/api/v1/tasks/1/relationships/assignees', [
            'data' => [],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);
        $this->assertDatabaseMissing('task_user', [
            'user_id' => $users[0]->id,
            'task_id' => 1,
        ])->assertDatabaseMissing('task_user', [
            'user_id' => $users[1]->id,
            'task_id' => 1,
        ])->assertDatabaseMissing('task_user', [
            'user_id' => $users[2]->id,
            'task_id' => 1,
        ]);
    }

    public function test_it_validates_that_the_id_member_is_given_when_updating_a_relationship()
    {
        $users = User::factory(5)->create();
        $project = Project::factory()->create();
        $task = Task::factory()->create();
        $auth = User::factory()->create();
        Sanctum::actingAs($auth);
        $this->patchJson('/api/v1/tasks/1/relationships/assignees', [
            'data' => [
                [
                    'type' => 'users',
                ],
            ],
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
                    ],
                ],
            ],
        ]);
    }

    public function test_it_validates_that_the_id_member_is_a_string_when_updating_a_relationship()
    {
        $users = User::factory(5)->create();
        $project = Project::factory()->create();
        $task = Task::factory()->create();
        $auth = User::factory()->create();
        Sanctum::actingAs($auth);
        $this->patchJson('/api/v1/tasks/1/relationships/assignees', [
            'data' => [
                [
                    'id' => 5,
                    'type' => 'users',
                ],
            ],
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
                    ],
                ],
            ],
        ]);
    }

    public function test_it_validates_that_the_type_member_is_given_when_updating_a_relationship()
    {
        $users = User::factory(5)->create();
        $project = Project::factory()->create();
        $task = Task::factory()->create();
        $auth = User::factory()->create();
        Sanctum::actingAs($auth);
        $this->patchJson('/api/v1/tasks/1/relationships/assignees', [
            'data' => [
                [
                    'id' => '5',
                ],
            ],
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
                    ],
                ],
            ],
        ]);
    }

    public function test_it_can_get_all_related_users_as_resource_objects_from_related_link()
    {
        $auth = User::factory()->create();
        $users = User::factory(3)->create();
        $project = Project::factory()->create(['user_id' => $auth->id]);
        $task = Task::factory()->create();
        $task->assignees()->sync($users->pluck('id'));
        Sanctum::actingAs($auth);
        $this->getJson('/api/v1/tasks/1/relationships/assignees', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200);
    }

    public function test_it_includes_related_resource_objects_when_an_include_query_param_is_given()
    {
        $users = User::factory(3)->create();
        $auth = User::factory()->create();
        Sanctum::actingAs($auth);
        $project = Project::factory()->create(['user_id' => $auth->id]);
        $task = Task::factory()->create();
        $task->assignees()->sync($users->pluck('id'));
        $this->getJson('/api/v1/tasks/1?include=assignees', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => '1',
                    'type' => 'tasks',
                    'relationships' => [
                        'assignees' => [
                            'links' => [
                                'self' => route('tasks.relationships.assignees', $project->id),
                                'related' => route('tasks.assignees', $project->id),
                            ],
                            'data' => [
                                [
                                    'id' => (string) $users->get(0)->id,
                                    'type' => 'users',
                                ],
                                [
                                    'id' => (string) $users->get(1)->id,
                                    'type' => 'users',
                                ],
                                [
                                    'id' => (string) $users->get(2)->id,
                                    'type' => 'users',
                                ],
                            ],
                        ],
                    ],
                ],
                'included' => [
                    [
                        'id' => $users[0]->id,
                        'type' => 'users',
                        'attributes' => [
                            'name' => $users[0]->name,
                            'created_at' => $users[0]->created_at->toJSON(),
                            'updated_at' => $users[0]->updated_at->toJSON(),
                        ],
                    ],
                    [
                        'id' => $users[1]->id,
                        'type' => 'users',
                        'attributes' => [
                            'name' => $users[1]->name,
                            'created_at' => $users[1]->created_at->toJSON(),
                            'updated_at' => $users[1]->updated_at->toJSON(),
                        ],
                    ],
                    [
                        'id' => $users[2]->id,
                        'type' => 'users',
                        'attributes' => [
                            'name' => $users[2]->name,
                            'created_at' => $users[2]->created_at->toJSON(),
                            'updated_at' => $users[2]->updated_at->toJSON(),
                        ],
                    ],
                ],
            ]);
    }

    public function test_it_does_not_include_related_resource_objects_when_an_include_query_param_is_not_given()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $task = Task::factory()->create();
        Sanctum::actingAs($user);
        $this->getJson('/api/v1/tasks/1', [
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
        $auth = User::factory()->create();
        Sanctum::actingAs($auth);
        $projects = Project::factory()->create();
        $tasks = Task::factory(3)->create();
        $users = User::factory(3)->create();

        $tasks->each(fn ($task, $key) => $key === 0 ? $task->assignees()->attach($users->pluck('id')) : null);

        $this->get('/api/v1/tasks?include=assignees', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            'data' => [
                [
                    'id' => '1',
                    'type' => 'tasks',
                    'attributes' => [
                        'title' => $tasks[0]->title,
                        'created_at' => $tasks[0]->created_at->toJSON(),
                        'updated_at' => $tasks[0]->updated_at->toJSON(),
                    ],
                    'relationships' => [
                        'assignees' => [
                            'links' => [
                                'self' => route('tasks.relationships.assignees', $tasks[0]->id),
                                'related' => route('tasks.assignees', $tasks[0]->id),
                            ],
                            'data' => [
                                [
                                    'id' => (string) $users->get(0)->id,
                                    'type' => 'users',
                                ],
                                [
                                    'id' => (string) $users->get(1)->id,
                                    'type' => 'users',
                                ],
                                [
                                    'id' => (string) $users->get(2)->id,
                                    'type' => 'users',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => '2',
                    'type' => 'tasks',
                    'attributes' => [
                        'title' => $tasks[1]->title,
                        'created_at' => $tasks[1]->created_at->toJSON(),
                        'updated_at' => $tasks[1]->updated_at->toJSON(),
                    ],
                    'relationships' => [
                        'assignees' => [
                            'links' => [
                                'self' => route('tasks.relationships.assignees', $tasks[1]->id),
                                'related' => route('tasks.assignees', $tasks[1]->id),
                            ],
                        ],
                    ],
                ],
                [
                    'id' => '3',
                    'type' => 'tasks',
                    'attributes' => [
                        'title' => $tasks[2]->title,
                        'created_at' => $tasks[2]->created_at->toJSON(),
                        'updated_at' => $tasks[2]->updated_at->toJSON(),
                    ],
                    'relationships' => [
                        'assignees' => [
                            'links' => [
                                'self' => route('tasks.relationships.assignees', $tasks[2]->id),
                                'related' => route('tasks.assignees', $tasks[2]->id),
                            ],
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'id' => $users[0]->id,
                    'type' => 'users',
                    'attributes' => [
                        'name' => $users[0]->name,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => $users[1]->id,
                    'type' => 'users',
                    'attributes' => [
                        'name' => $users[1]->name,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => $users[2]->id,
                    'type' => 'users',
                    'attributes' => [
                        'name' => $users[2]->name,
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ],
                ],
            ],
        ]);
    }

    public function test_it_does_not_include_related_resource_objects_for_a_collection_when_an_include_param_is_not_given()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $task = Task::factory()->create();
        Sanctum::actingAs($user);
        $this->get('/api/v1/tasks', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)
            ->assertJsonMissing([
                'included' => [],
            ]);
    }

    public function test_it_only_includes_a_related_resource_object_once_for_a_collection()
    {
        $auth = User::factory()->create();
        Sanctum::actingAs($auth);
        $users = User::factory(3)->create();
        $project = Project::factory()->create();
        $tasks = Task::factory(3)->create();

        $tasks->each(fn ($task) => $task->assignees()->attach($users->pluck('id')));

        $this->get('/api/v1/tasks?include=assignees', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            'data' => [
                [
                    'id' => '1',
                    'type' => 'tasks',
                    'attributes' => [
                        'title' => $tasks[0]->title,
                        'created_at' => $tasks[0]->created_at->toJSON(),
                        'updated_at' => $tasks[0]->updated_at->toJSON(),
                    ],
                    'relationships' => [
                        'assignees' => [
                            'links' => [
                                'self' => route('tasks.relationships.assignees', $tasks[0]->id),
                                'related' => route('tasks.assignees', $tasks[0]->id),
                            ],
                            'data' => [
                                [
                                    'id' => (string) $users->get(0)->id,
                                    'type' => 'users',
                                ],
                                [
                                    'id' => (string) $users->get(1)->id,
                                    'type' => 'users',
                                ],
                                [
                                    'id' => (string) $users->get(2)->id,
                                    'type' => 'users',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => '2',
                    'type' => 'tasks',
                    'attributes' => [
                        'title' => $tasks[1]->title,
                        'created_at' => $tasks[1]->created_at->toJSON(),
                        'updated_at' => $tasks[1]->updated_at->toJSON(),
                    ], 'relationships' => [
                        'assignees' => [
                            'links' => [
                                'self' => route('tasks.relationships.assignees', $tasks[1]->id),
                                'related' => route('tasks.assignees', $tasks[1]->id),
                            ],
                            'data' => [
                                [
                                    'id' => (string) $users->get(0)->id,
                                    'type' => 'users',
                                ],
                                [
                                    'id' => (string) $users->get(1)->id,
                                    'type' => 'users',
                                ],
                                [
                                    'id' => (string) $users->get(2)->id,
                                    'type' => 'users',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => '3',
                    'type' => 'tasks',
                    'attributes' => [
                        'title' => $tasks[2]->title,
                        'created_at' => $tasks[2]->created_at->toJSON(),
                        'updated_at' => $tasks[2]->updated_at->toJSON(),
                    ],
                    'relationships' => [
                        'assignees' => [
                            'links' => [
                                'self' => route('tasks.relationships.assignees', $tasks[2]->id),
                                'related' => route('tasks.assignees', $tasks[2]->id),
                            ],
                            'data' => [
                                [
                                    'id' => (string) $users->get(0)->id,
                                    'type' => 'users',
                                ],
                                [
                                    'id' => (string) $users->get(1)->id,
                                    'type' => 'users',
                                ],
                                [
                                    'id' => (string) $users->get(2)->id,
                                    'type' => 'users',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'id' => $users[0]->id,
                    'type' => 'users',
                    'attributes' => [
                        'name' => $users[0]->name,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ],
                ], [
                    'id' => $users[1]->id,
                    'type' => 'users',
                    'attributes' => [
                        'name' => $users[1]->name,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => $users[2]->id,
                    'type' => 'users',
                    'attributes' => [
                        'name' => $users[2]->name,
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ],
                ],
            ],
        ])->assertJsonMissing([
            'included' => [
                [
                    'id' => '1',
                    'type' => 'users',
                    'attributes' => [
                        'name' => $users[0]->name,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '2',
                    'type' => 'users',
                    'attributes' => [
                        'name' => $users[1]->name,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '3',
                    'type' => 'users',
                    'attributes' => [
                        'name' => $users[2]->name,
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '1',
                    'type' => 'users',
                    'attributes' => [
                        'name' => $users[0]->name,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '2',
                    'type' => 'users',
                    'attributes' => [
                        'name' => $users[1]->name,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '3',
                    'type' => 'users',
                    'attributes' => [
                        'name' => $users[2]->name,
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '1',
                    'type' => 'users',
                    'attributes' => [
                        'name' => $users[0]->name,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '2',
                    'type' => 'users',
                    'attributes' => [
                        'name' => $users[1]->name,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ],
                ],
                [
                    'id' => '3',
                    'type' => 'users',
                    'attributes' => [
                        'name' => $users[2]->name,
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ],
                ],
            ],
        ]);
    }
}
