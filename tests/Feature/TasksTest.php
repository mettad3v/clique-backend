<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TasksTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_returns_a_task_as_a_resource_object()
    {
        $task = Task::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('api/v1/tasks/1', [ 
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])->assertOk()->assertJson([
            "data" => [
                "id" => '1',
                "type" => "tasks",
                "attributes" => [
                    'title' => $group->title,
                    'category_id' => $group->category_id,
                    'project_id' => $group->project_id,
                    'group_id' => $group->group_id,
                    'user_id' => $group->user_id,
                    'assignees' => $group->assignees,
                    'task_unique_id' => $group->task_unique_id,
                    'deadline' => $group->deadline,
                    'description' => $group->description,
                    'created_at' => $group->created_at->toJSON(),
                    'updated_at' => $group->updated_at->toJSON(),
                ]
            ]
        ]);

        
    }

    public function test_It_returns_all_groups_as_a_collection_of_resource_objects()
    {
       
    }

    public function test_It_can_paginate_groups_through_a_page_query_parameter()
    {
        
    }

    public function it_can_sort_groups_by_name_through_a_sort_query_parameter()
    {
        
    }

    public function it_can_sort_groups_by_name_in_descending_order_through_a_sort_query_parameter()
    {
        
    }

    public function test_it_can_sort_groups_by_multiple_sort_params_through_a_sort_query_parameter()
    {
        
    }

    public function test_it_can_sort_groups_by_multiple_sort_params_including_in_descending_order_through_a_sort_query_parameter()
    {
        
    }


    public function test_it_can_create_a_group_from_a_resource_object()
    {
       
    }

    public function test_it_validates_that_the_type_member_is_given_when_creating_a_group()
    {
       
    }

    public function test_it_validates_that_the_type_member_is_given_when_updating_a_group()
    {
        
    }

    public function test_it_validates_that_the_type_member_has_the_value_of_groups_when_creating_a_group()
    {
        
    }

    public function test_it_validates_that_the_type_member_has_the_value_of_groups_when_updating_a_group()
    {
        
    }

    public function test_it_validates_that_a_title_attribute_has_been_given_when_creating_a_group()
    {
        
    }

    public function test_it_validates_that_the_attributes_member_has_been_given_when_updating_a_group()
    {
        
    }

    public function test_it_validates_that_a_title_attribute_is_a_string_when_creating_a_group()
    {
        
    }

    public function test_it_validates_that_a_title_attribute_is_a_string_when_updating_a_group()
    {
        
    }

    public function test_it_validates_that_an_id_member_is_a_string_when_updating_a_group()
    {
        
    }
    public function test_it_validates_that_the_attributes_member_has_been_given_when_creating_a_group()
    {
       
    }

    public function test_it_validates_that_the_attributes_member_is_an_object_given_when_creating_a_group()
    {
        
    }

    public function test_it_validates_that_the_attributes_member_is_an_object_given_when_updating_a_group()
    {
        
    }

    public function test_it_can_update_an_group_from_a_resource_object()
    {
        
    }

    public function test_it_validates_that_an_id_member_is_given_when_updating_a_group()
    {
        
    }

    public function test_it_can_delete_a_group_through_a_delete_request()
    {
       
    }
}
