<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $posts = [
            [
                'name' => "SA1",
                'description' => 'Super Agent 1',
                'status' => 1,
                'created_at' => new \DateTime,
                'updated_at' => null,
            ],
            [
                'name' => "SA2",
                'description' => 'Super Agent 2',
                'status' => 1,
                'created_at' => new \DateTime,
                'updated_at' => null,
            ],
            [
                'name' => "SA3",
                'description' => 'Super Agent 3',
                'status' => 1,
                'created_at' => new \DateTime,
                'updated_at' => null,
            ],
            [
                'name' => "AGENT1",
                'description' => 'Agent 1',
                'status' => 1,
                'created_at' => new \DateTime,
                'updated_at' => null,
            ],
            [
                'name' => "AGENT2",
                'description' => 'Agent 2',
                'status' => 1,
                'created_at' => new \DateTime,
                'updated_at' => null,
            ],
            [
                'name' => "B2B",
                'description' => 'Business to Business',
                'status' => 1,
                'created_at' => new \DateTime,
                'updated_at' => null,
            ],
            [
                'name' => "Retail",
                'description' => 'Retail',
                'status' => 1,
                'created_at' => new \DateTime,
                'updated_at' => null,
            ],
            [
                'name' => "MARKETPLACE",
                'description' => 'Marketplace',
                'status' => 1,
                'created_at' => new \DateTime,
                'updated_at' => null,
            ]
        ];

        DB::table('levels')->insert($posts);
    }
}
