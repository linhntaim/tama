<?php

namespace Database\Seeders;

use App\Models\User;

class TrialUserSeeder extends Seeder
{
    public function run()
    {
        User::factory()->count(1000)->create();
    }
}