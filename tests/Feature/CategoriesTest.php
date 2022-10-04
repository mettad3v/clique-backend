<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CategoriesTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_returns_a_category_as_a_resource_object()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // dd($category->title);
        $this->getJson('/api/v1/categories/1', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertOk()->assertJson([
            "data" => [
                "id" => '1',
                "type" => "categories",
                "attributes" => [
                    'title' => $category->title,
                    'created_at' => $category->created_at->toJSON(),
                    'updated_at' => $category->updated_at->toJSON(),
                ]
            ]
        ]);
    }

    public function test_It_returns_all_categories_as_a_collection_of_resource_objects()
    {
        $categories = Category::factory(3)->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->get('/api/v1/categories', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '1',
                    "type" => "categories",
                    "attributes" => [
                        'title' => $categories[0]->title,
                        'created_at' => $categories[0]->created_at->toJSON(),
                        'updated_at' => $categories[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "categories",
                    "attributes" => [
                        'title' => $categories[1]->title,
                        'created_at' => $categories[1]->created_at->toJSON(),
                        'updated_at' => $categories[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '3',
                    "type" => "categories",
                    "attributes" => [
                        'title' => $categories[2]->title,
                        'created_at' => $categories[2]->created_at->toJSON(),
                        'updated_at' => $categories[2]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);;
    }

    public function test_It_can_paginate_categories_through_a_page_query_parameter()
    {
        $categories = Category::factory(10)->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->get('/api/v1/categories?page[size]=5&page[number]=1', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '1',
                    "type" => "categories",
                    "attributes" => [
                        'title' => $categories[0]->title,
                        'created_at' => $categories[0]->created_at->toJSON(),
                        'updated_at' => $categories[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "categories",
                    "attributes" => [
                        'title' => $categories[1]->title,
                        'created_at' => $categories[1]->created_at->toJSON(),
                        'updated_at' => $categories[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '3',
                    "type" => "categories",
                    "attributes" => [
                        'title' => $categories[2]->title,
                        'created_at' => $categories[2]->created_at->toJSON(),
                        'updated_at' => $categories[2]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '4',
                    "type" => "categories",
                    "attributes" => [
                        'title' => $categories[3]->title,
                        'created_at' => $categories[3]->created_at->toJSON(),
                        'updated_at' => $categories[3]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '5',
                    "type" => "categories",
                    "attributes" => [
                        'title' => $categories[4]->title,
                        'created_at' => $categories[4]->created_at->toJSON(),
                        'updated_at' => $categories[4]->updated_at->toJSON(),
                    ]
                ],
            ],
            'links' => [
                'first' => route('categories.index', ['page[size]' => 5, 'page[number]' => 1]),
                'last' => route('categories.index', ['page[size]' => 5, 'page[number]' => 2]),
                'prev' => null,
                'next' => route('categories.index', ['page[size]' => 5, 'page[number]' => 2]),
            ]
        ]);
    }


    public function it_can_sort_categories_by_created_at_date_in_ascending_order_through_a_sort_query_parameter()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $categories = collect([
            'todo',
            'blocking',
            'blocked',
        ])->map(function ($title) {
            if ($title === 'todo') {
                return Category::factory()->create([
                    'title' => $title,
                    'created_at' => now()->addSeconds(3),
                ]);
            }

            return Category::factory()->create([
                'title' => $title,
            ]);
        });
        $this->get('/api/v1/categories?sort=created_at', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '3',
                    "type" => "categories",
                    "attributes" => [
                        'title' => 'blocked',
                        'created_at' => $categories[2]->created_at->toJSON(),
                        'updated_at' => $categories[2]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "categories",
                    "attributes" => [
                        'title' => 'blocking',
                        'created_at' => $categories[1]->created_at->toJSON(),
                        'updated_at' => $categories[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '1',
                    "type" => "categories",
                    "attributes" => [
                        'title' => 'todo',
                        'created_at' => $categories[0]->created_at->toJSON(),
                        'updated_at' => $categories[0]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }

    public function it_can_sort_categories_by_created_at_date_in_descending_order_through_a_sort_query_parameter()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $categories = collect([
            'todo',
            'blocking',
            'blocked',
        ])->map(function ($title) {
            if ($title === 'todo') {
                return Category::factory()->create([
                    'title' => $title,
                    'created_at' => now()->addSeconds(3),
                ]);
            }

            return Category::factory()->create([
                'title' => $title,
            ]);
        });
        $this->get('/api/v1/categories?sort=-created_at', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '1',
                    "type" => "categories",
                    "attributes" => [
                        'title' => 'todo',
                        'created_at' => $categories[0]->created_at->toJSON(),
                        'updated_at' => $categories[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "categories",
                    "attributes" => [
                        'title' => 'blocking',
                        'created_at' => $categories[1]->created_at->toJSON(),
                        'updated_at' => $categories[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '3',
                    "type" => "categories",
                    "attributes" => [
                        'title' => 'blocked',
                        'created_at' => $categories[2]->created_at->toJSON(),
                        'updated_at' => $categories[2]->updated_at->toJSON(),
                    ]
                ]
            ]
        ]);
    }

    public function test_it_can_paginate_categories_through_a_page_query_parameter_and_show_different_pages()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $categories = Category::factory(10)->create();
        $this->get('/api/v1/categories?page[size]=5&page[number]=2', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '6',
                    "type" => "categories",
                    "attributes" => [
                        'title' => $categories[5]->title,
                        'created_at' => $categories[5]->created_at->toJSON(),
                        'updated_at' => $categories[5]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '7',
                    "type" => "categories",
                    "attributes" => [
                        'title' => $categories[6]->title,
                        'created_at' => $categories[6]->created_at->toJSON(),
                        'updated_at' => $categories[6]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '8',
                    "type" => "categories",
                    "attributes" => [
                        'title' => $categories[7]->title,
                        'created_at' => $categories[7]->created_at->toJSON(),
                        'updated_at' => $categories[7]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '9',
                    "type" => "categories",
                    "attributes" => [
                        'title' => $categories[8]->title,
                        'created_at' => $categories[8]->created_at->toJSON(),
                        'updated_at' => $categories[8]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '10',
                    "type" => "categories",
                    "attributes" => [
                        'title' => $categories[9]->title,
                        'created_at' => $categories[9]->created_at->toJSON(),
                        'updated_at' => $categories[9]->updated_at->toJSON(),
                    ]
                ],
            ],
            'links' => [
                'first' => route('categories.index', ['page[size]' => 5, 'page[number]' => 1]),
                'last' => route('categories.index', ['page[size]' => 5, 'page[number]' => 2]),
                'prev' => route('categories.index', ['page[size]' => 5, 'page[number]' => 1]),
                'next' => null,
            ]
        ]);
    }
}
