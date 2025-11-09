<?php

namespace Database\Seeders;

use App\Models\OrderLead;
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
        OrderLead::factory(10)->create();
    }
}
