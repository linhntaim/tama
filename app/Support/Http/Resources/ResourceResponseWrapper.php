<?php

namespace App\Support\Http\Resources;

/**
 * @property IWrappedResource $resource
 */
trait ResourceResponseWrapper
{
    protected function wrapper(): ?string
    {
        return $this->resource->getWrapped();
    }

    protected function haveDefaultWrapperAndDataIsUnwrapped($data): bool
    {
        return $this->wrapper() && (is_null($data) || !array_key_exists($this->wrapper(), $data));
    }

    protected function haveAdditionalInformationAndDataIsUnwrapped($data, $with, $additional): bool
    {
        return (!empty($with) || !empty($additional))
            && (!$this->wrapper()
                || (is_null($data) || !array_key_exists($this->wrapper(), $data)));
    }

    protected function wrap($data, $with = [], $additional = []): array
    {
        return parent::wrap(
            $this->resource instanceof Resource ? nullify_empty_array($data) : $data,
            $with,
            $additional
        );
    }
}