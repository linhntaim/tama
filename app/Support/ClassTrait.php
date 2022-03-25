<?php

/**
 * Base
 */

namespace App\Support;

use Closure;

trait ClassTrait
{
    protected array $classAttributes = [];

    protected function classAttribute($name, Closure $value): mixed
    {
        return $this->classAttributes[$name] ?? ($this->classAttributes[$name] = $value());
    }

    protected function className(): string
    {
        return $this->classAttribute('name', function () {
            return static::class;
        });
    }

    protected function classBasename(): string
    {
        return $this->classAttribute('basename', function () {
            return class_basename($this->className());
        });
    }

    protected function classSnakedName(): string
    {
        return $this->classAttribute('snaked_name', function () {
            return str($this->classBasename())
                ->snake()
                ->toString();
        });
    }

    protected function classChainedName(): string
    {
        return $this->classAttribute('chained_name', function () {
            return str($this->classBasename())
                ->snake('-')
                ->toString();
        });
    }

    protected function classFriendlyName(): string
    {
        return $this->classAttribute('friendly_name', function () {
            return str($this->classBasename())
                ->snake(' ')
                ->title()
                ->toString();
        });
    }
}