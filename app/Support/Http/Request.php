<?php

namespace App\Support\Http;

use Illuminate\Http\Request as BaseRequest;

class Request extends BaseRequest
{
    protected ?string $headerContentType = null;

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

    public function isMultipartFormData(?string &$boundary = null): bool
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

    public function __toString(): string
    {
        $bagStringGroup = new class {
            /**
             * @var BagString[]|array
             */
            protected array $bagStrings = [];

            public function putBag(string $group, ?BagString $bagString): static
            {
                if (is_null($bagString)) {
                    return $this;
                }
                $this->bagStrings[$group] = $bagString;
                return $this;
            }

            public function __toString(): string
            {
                $nameLength = max(array_map(function (BagString $bagString) {
                    return $bagString->getNameLength();
                }, $this->bagStrings));
                $stringified = [];
                foreach ($this->bagStrings as $group => $bagString) {
                    $stringified[] = sprintf('[%s]', $group);
                    $stringified[] = (string)$bagString->setNameLength($nameLength);
                }
                return implode(PHP_EOL, $stringified);
            }
        };
        return implode(PHP_EOL, [
            sprintf('%s %s %s', $this->getMethod(), $this->getRequestUri(), $this->server->get('SERVER_PROTOCOL')),
            $bagStringGroup
                ->putBag('Headers', HeaderBagString::create($this->headers->all()))
                ->putBag('Cookies', BagString::create($this->cookies->all()))
                ->putBag('Sessions', BagString::create($this->session->all()))
                ->putBag('Query', BagString::create($this->query()))
                ->putBag('Request', BagString::create($this->post()))
                ->putBag('Files', FileBagString::create($this->allFiles()))
                ->putBag('Server', BagString::create($this->server->all())),
        ]);
    }
}