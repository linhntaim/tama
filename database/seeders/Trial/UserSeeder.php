<?php

namespace Database\Seeders\Trial;

use App\Models\User;
use Database\Seeders\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::factory()->count(1000)->create();
    }
}
