<?php

namespace App\Models;

use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Models\IUserProvider;
use App\Support\Models\Model;
use App\Support\Models\ModelProvider;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property User|null $model
 * @method  User|null model(Model|callable|int|string $model = null, bool $byUnique = true)
 * @method  User|null executeFirst(Builder $query)
 * @method  User|null firstByKey(int|string $key)
 * @method  User createWithAttributes(array $attributes = [])
 */
class UserProvider extends ModelProvider implements IUserProvider
{
    public string $modelClass = User::class;

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function system(): ?User
    {
        return $this->skipProtected()->firstByKey(User::SYSTEM_ID);
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function owner(): ?User
    {
        return $this->skipProtected()->firstByKey(User::OWNER_ID);
    }

    protected function whereByEmail(Builder $query, $value): Builder
    {
        return $this->whereLike($query, 'email', $value);
    }

    protected function whereByName(Builder $query, $value): Builder
    {
        return $this->whereLike($query, 'name', $value);
    }

    protected function whereByCreatedFrom(Builder $query, $value): Builder
    {
        return $query->where('created_at', '>=', $value);
    }

    protected function whereByCreatedTo(Builder $query, $value): Builder
    {
        return $query->where('created_at', '<=', $value);
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function firstByUsername(string $username, $value): ?User
    {
        return $this->executeFirst($this->whereQuery()->where($username, $value));
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function firstByEmail(string $email): ?User
    {
        return $this->executeFirst($this->whereQuery()->where('email', $email));
    }
}
