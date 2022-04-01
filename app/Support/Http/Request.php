<?php

/**
 * Base
 */

namespace App\Support\Http;

use Illuminate\Http\Request as BaseRequest;

class Request extends BaseRequest
{
    public function headerJson(string $key, ?array $default = null): ?array
    {
        if (is_null($header = $this->header($key))
            || is_null($header = json_decode_array($header))) {
            return $default;
        }
        return $header;
    }

    public function cookieJson(string $key, ?array $default = null): ?array
    {
        if (is_null($cookie = $this->cookie($key))
            || is_null($cookie = json_decode_array($cookie))) {
            return $default;
        }
        return $cookie;
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