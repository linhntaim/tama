<?php

namespace App\Models;

use App\Support\Models\Contracts\UserProvider as UserProviderContract;
use App\Support\Models\Model;
use App\Support\Models\ModelProvider;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property User|null $model
 * @method User|null model(Model|callable|int|string $model = null, bool $byUnique = true)
 * @method User|null first(array $conditions = [])
 * @method User|null firstByKey(int|string $key)
 * @method User firstOrCreateWithAttributes(array $attributes = [], array $values = [])
 * @method User createWithAttributes(array $attributes = [])
 */
class UserProvider extends ModelProvider implements UserProviderContract
{
    public string $modelClass = User::class;

    public function system(): ?User
    {
        return $this->skipProtected()->firstByKey(User::SYSTEM_ID);
    }

    public function owner(): ?User
    {
        return $this->skipProtected()->firstByKey(User::OWNER_ID);
    }

    protected function whereByCreatedFrom(Builder $query, string $value): Builder
    {
        return $query->where('created_at', '>=', $value);
    }

    protected function whereByCreatedTo(Builder $query, string $value): Builder
    {
        return $query->where('created_at', '<=', $value);
    }

    public function firstByUsername(string $username, $value): ?User
    {
        return $this->first([$username => $value]);
    }

    public function firstByEmail(string $email): ?User
    {
        return $this->firstByUsername('email', $email);
    }

    public function firstByProvider(string $provider, string $providerId): ?User
    {
        return $this->firstByUsername('socials', function ($query) use ($provider, $providerId) {
            $query->where('provider', $provider)
                ->where('provider_id', $providerId);
        });
    }
}
