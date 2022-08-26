<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder as BaseSeeder;

abstract class Seeder extends BaseSeeder
{
    abstract public function run(...$parameters): void;
}
