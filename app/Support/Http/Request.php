<?php

namespace App\Support\Http;

use Illuminate\Http\Request as BaseRequest;

class Request extends BaseRequest
{
    public function expectsJson(): bool
    {
        return parent::expectsJson()
            || $this->is(config_starter('routes.json'));
    }

    public function perPage($default = 10): int
    {
        $perPage = (int)$this->input('per_page');
        return $perPage < 1 ? $default : $perPage;
    }

    public function sortBy(string $default, array $allowed = []): ?string
    {
        if ($this->if('sort_by', $by, true)) {
            return !count($allowed) || in_array($by, $allowed) ? $by : null;
        }
        return $default;
    }

    public function sortAscending(bool $default = true): bool
    {
        return $this->has('sort_desc') ? false : $default;
    }

    public function if(string $key, &$input, bool $strict = false): bool
    {
        $input = $this->input($key);
        return $this->has($key) && (!$strict || !is_null($input));
    }

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
             * @var array|BagString[]
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
                ->putBag('Sessions', BagString::create($this->session?->all()))
                ->putBag('Query', BagString::create($this->query()))
                ->putBag('Request', BagString::create($this->post()))
                ->putBag('Files', FileBagString::create($this->allFiles()))
                ->putBag('Server', BagString::create($this->server->all())),
        ]);
    }
}
