<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    public function test_a_users_ID_is_a_UUID_instead_of_an_integer()
    {
        $user = User::factory()->create();
        $this->assertFalse(is_int($user->id));
        $this->assertEquals(36, strlen($user->id));
    }
}
