<?php

namespace App\Support\Models\Relations;

use Illuminate\Database\Eloquent\Relations\MorphToMany as BaseMorphToMany;

class MorphToMany extends BaseMorphToMany
{
    public function setMorphClass(string $morphClass): static
    {
        $this->morphClass = $morphClass;
        return $this;
    }
}