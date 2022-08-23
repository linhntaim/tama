<?php

namespace Database\Seeders;

use App\Models\UserProvider;
use App\Support\Client\DateTimer;
use App\Support\Facades\App;
use Illuminate\Support\Str;

class DefaultUsersSeeder extends Seeder
{
    protected string $defaultPassword = '12345678';

    public function run()
    {
        $this->createUser(config_starter('database.seeders.users.system'), 'System');
        $this->createUser(config_starter('database.seeders.users.owner'), 'Owner');
    }

    protected function randomPassword(): string
    {
        return Str::random(8);
    }

    protected function createUser(array $attributes, string $defaultName = 'User')
    {
        (new UserProvider())->updateOrCreateWithAttributes([
            'email' => $email = ($attributes['email'] ?? Str::snake($defaultName) . '@' . parse_url(config('app.url'), PHP_URL_HOST)),
        ], [
            'name' => $name = ($attributes['name'] ?? Str::ucfirst($defaultName)),
            'password' => $password = ($attributes['password'] ?? (App::isProduction() ? $this->randomPassword() : $this->defaultPassword)),
            'email_verified_at' => DateTimer::databaseNow(),
        ]);
        $this->command->line(sprintf('<comment>%s</comment>: %s / %s', $name, $email, $password));
    }
}
