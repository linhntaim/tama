<?php

/**
 * Base
 */

namespace App\Support;

class EnvironmentFile
{
    protected string $path;

    protected bool $exists;

    protected string $content;

    protected bool $modified;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->exists = file_exists($this->path);
        $this->content = $this->exists ? file_get_contents($this->path) : null;
        $this->modified = false;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function save(): static
    {
        if (!$this->exists) {
            return $this;
        }

        if ($this->modified) {
            file_put_contents($this->path, $this->content);
            $this->modified = false;
        }
        return $this;
    }

    public function has(string $key): bool
    {
        return $this->exists && preg_match("/^$key=/m", $this->content) === 1;
    }

    public function filled(string $key, &$value = null): bool
    {
        $matched = $this->exists && preg_match("/^$key=([^\r\n]+)/m", $this->content, $matches) === 1;
        $value = $matched ? ($matches[1] ?? null) : null;
        return $matched;
    }

    protected function compose(string $key, mixed $value = null): string
    {
        if (is_null($value)) {
            $value = '';
        }
        elseif (is_array($value)) {
            $value = json_encode_readable($value);
        }
        elseif (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        if (mb_strpos($value, ' ') !== false) {
            $value = mb_strpos($value, '"') !== false
                ? sprintf('"%s"', str_replace('"', '\\"', $value))
                : sprintf('"%s"', $value);
        }
        return sprintf('%s=%s', $key, $value);
    }

    public function fill(string|array $key, mixed $value = null): static
    {
        if (!$this->exists) {
            return $this;
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->fill($k, $v);
            }
            return $this;
        }

        $key = strtoupper($key);
        if ($this->has($key)) {
            $this->content = preg_replace(
                "/^$key=[^\r\n]*/m",
                $this->compose($key, $value),
                $this->content
            );
        }
        else {
            $this->content .= PHP_EOL . $this->compose($key, $value);
        }
        $this->modified = true;
        return $this;
    }

    public function clear(string $key): static
    {
        $this->filled($key) && $this->fill($key, '');
        return $this;
    }
}
