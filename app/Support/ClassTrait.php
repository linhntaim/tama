<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Str;

trait ClassTrait
{
    protected array $classAttributes = [];

    protected function classAttribute($name, Closure $value): mixed
    {
        return $this->classAttributes[$name] ?? ($this->classAttributes[$name] = $value());
    }

    public function className(): string
    {
        return $this->classAttribute('name', function () {
            return static::class;
        });
    }

    public function classBasename(): string
    {
        return $this->classAttribute('basename', function () {
            return class_basename($this->className());
        });
    }

    public function classSnakedName(): string
    {
        return $this->classAttribute('snaked_name', function () {
            return Str::snake($this->classBasename());
        });
    }

    public function classChainedName(): string
    {
        return $this->classAttribute('chained_name', function () {
            return Str::snake($this->classBasename(), '-');
        });
    }

    public function classFriendlyName(): string
    {
        return $this->classAttribute('friendly_name', function () {
            return Str::title(Str::snake($this->classBasename(), ' '));
        });
    }
}
