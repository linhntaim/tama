<?php

namespace App\Support\Models\Relations;

use Illuminate\Database\Eloquent\Relations\MorphToMany as BaseMorphToMany;

class MorphToMany extends BaseMorphToMany
{
    public function setMorphClass(string $morphClass): static
    {
        $this->morphClass = $morphClass;
        $query = $this->query->getQuery();
        $query->wheres[1]['value'] = $this->morphClass;
        $query->bindings['where'][1] = $this->morphClass;
        return $this;
    }
}