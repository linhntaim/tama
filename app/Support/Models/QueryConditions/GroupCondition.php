<?php

namespace App\Support\Models\QueryConditions;

use Illuminate\Database\Eloquent\Builder;

class GroupCondition extends QueryCondition
{
    protected array $groups;

    public function __construct(array $groups)
    {
        $this->setGroups($groups);
    }

    public function setGroups(array $groups): static
    {
        $this->groups = $groups;
        return $this;
    }

    public function __invoke(Builder $query): Builder
    {
        return $query->groupBy($this->groups);
    }
}
