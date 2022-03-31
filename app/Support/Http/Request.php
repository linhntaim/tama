<?php

namespace App\Support\Http;

use Illuminate\Http\Request as BaseRequest;

class Request extends BaseRequest
{
    protected string|null $headerContentType = null;

    public function getHeaderContentType(): ?string
    {
        return is_null($this->headerContentType)
            ? ($this->headerContentType = $this->headers->get('CONTENT_TYPE'))
            : $this->headerContentType;
    }

    public function getHeaderMimeType(): ?string
    {
        $mimeType = $this->getHeaderContentType();
        if ($mimeType && false !== ($pos = strpos($mimeType, ';'))) {
            $mimeType = trim(substr($mimeType, 0, $pos));
        }
        return $mimeType;
    }

    public function isMultipartFormData(string|null &$boundary = null): bool
    {
        $boundary = null;
        if ('multipart/form-data' === $this->getHeaderMimeType()) {
            if (1 === preg_match('/boundary=([^\s]+)/', $this->getHeaderContentType(), $matches)) {
                $boundary = $matches[1] ?? null;
            }
            return true;
        }
        return false;
    }
}