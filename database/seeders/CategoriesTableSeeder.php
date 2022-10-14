<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\Category::factory(8)->create();

        $allowed = ['backlog', 'todo', 'blocked', 'progress', 'ready', 'done', 'others', 'wontdo', 'blocking'];

        foreach ($allowed as $title) {
            Category::create([
                'title' => $title,
            ]);
        }
    }
}
