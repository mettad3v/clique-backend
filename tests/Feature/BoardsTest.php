<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Board;
use App\Models\Project;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class BoardsTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_can_create_a_board_from_a_json_api_resource_object()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $board = Board::factory()->create(['title' => 'test', 'project_id' => 1]);
        Sanctum::actingAs($user);

        // dd($group);
        $this->postJson('/api/v1/boards', [
            'data' => [
                'type' => 'boards',
                'attributes' => [
                    'title' => 'test'
                ],
                'relationships' => [
                    'project' => [
                        'data' => [
                            'id' => (string) $project->id,
                            'type' => 'projects',
                        ],
                    ],
                    'creator' => [
                        'data' => [
                            'id' => (string) $user->id,
                            'type' => 'users',
                        ],
                    ],
                ],
            ],
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200);

        $this->assertDatabaseMissing('boards', [
            'id' => 2,
            'title' => 'test',
        ]);
    }
}
