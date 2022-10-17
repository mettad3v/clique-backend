<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // GroupsTableSeeder::class,
            UsersTableSeeder::class,
            CategoriesTableSeeder::class,
            // TasksTableSeeder::class,
            ProjectsTableSeeder::class,
        ]);
    }
}
