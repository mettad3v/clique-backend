<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GroupsRelationshipsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_it_returns_a_relationship_to_tasks_adhering_to_json_api()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $tasks = Task::factory(3)->create();
        $group = Group::factory()->create();
        $group->tasks()->saveMany($tasks);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/groups/{$group->id}?include=tasks", [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => '1',
                    'type' => 'groups',
                    'relationships' => [
                        'tasks' => [
                            'links' => [
                                'self' => route('groups.relationships.tasks', $group->id),
                                'related' => route('groups.tasks', $group->id),
                            ],
                            'data' => [
                                [
                                    'id' => $tasks->get(0)->id,
                                    'type' => 'tasks',
                                ],
                                [
                                    'id' => $tasks->get(1)->id,
                                    'type' => 'tasks',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_a_relationship_link_to_tasks_returns_all_related_tasks_as_resource_id_ob()
    {
        $project = Project::factory()->create();
        $group = Group::factory()->create();
        $tasks = Task::factory(3)->create();
        $group->tasks()->saveMany($tasks);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/groups/{$group->id}/relationships/tasks", [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'id' => '1',
                        'type' => 'tasks',
                    ],
                    [
                        'id' => '2',
                        'type' => 'tasks',
                    ],
                    [
                        'id' => '3',
                        'type' => 'tasks',
                    ],
                ],
            ]);
    }

    public function test_it_can_modify_relationships_to_groups_and_add_new_relationships()
    {
        // $this->withoutExceptionHandling();
        $project = Project::factory()->create();
        $group = Group::factory()->create();
        $tasks = Task::factory(10)->create();
        $user = User::factory()->create();
        // dd($group->tasks);
        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/groups/{$group->id}/relationships/tasks", [
            'data' => [
                [
                    'id' => '5',
                    'type' => 'tasks',
                ],
                [
                    'id' => '6',
                    'type' => 'tasks',
                ],
            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);

        // dd($group->tasks);
        $this->assertDatabaseHas('tasks', [
            'id' => 5,
            'group_id' => 1,
        ])->assertDatabaseHas('tasks', [
            'id' => 6,
            'group_id' => 1,
        ]);
    }
}
