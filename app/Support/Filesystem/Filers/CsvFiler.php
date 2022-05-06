<?php

namespace App\Support\Filesystem\Filers;

use App\Support\Exceptions\FileException;
use RuntimeException;
use SplFileObject;

/**
 * @method static writeAll(array $data, bool $close = true)
 */
class CsvFiler extends Filer
{
    public static function create(?string $in = null, ?string $name = null, ?string $extension = null): static
    {
        return parent::create($in, $name, 'csv');
    }

    protected bool $skipEmpty = true;

    protected bool|array $withHeaders = false;

    protected bool $hasHeaders = false;

    protected bool $headersRead = false;

    protected int $headersLine = -1;

    protected ?array $headers = null;

    public function setControl(string $separator = ',', string $enclosure = '"', string $escape = '\\'): static
    {
        $this->openingFile->setCsvControl($separator, $enclosure, $escape);
        return $this;
    }

    /**
     * @param array $data
     * @return static
     * @throws FileException
     */
    public function write($data): static
    {
        ++$this->writingLine;
        if ($this->openingFile->fputcsv($data) === false) {
            throw new FileException('Cannot write into the file.');
        }
        return $this;
    }

    /**
     * @param array $data
     * @return static
     * @throws FileException
     */
    public function writeln($data): static
    {
        return $this->write($data);
    }

    public function hasHeaders(bool $hasHeaders = true): static
    {
        $this->hasHeaders = $hasHeaders;
        return $this;
    }

    public function withHeaders(bool|array $withHeaders = true): static
    {
        $this->withHeaders = $withHeaders;
        return $this;
    }

    /**
     * @throws FileException
     */
    public function readHeaders(): ?array
    {
        if ($this->hasHeaders && !$this->headersRead) {
            $readingLine = $this->readingLine;
            $this->seekingLine(0);

            while (!$this->headersRead) {
                ++$this->readingLine;
                if (!$this->openingFile->eof()) {
                    if (($read = $this->openingFile->fgetcsv()) === false) {
                        throw new FileException(sprintf('Could not read at line %d.', $this->readingLine()));
                    }
                    if ($this->skipEmpty
                        && [] === array_filter($read, function ($value) {
                            return !null_or_empty_string($value);
                        })) {
                        continue;
                    }
                    $this->headers = $read;
                }
                $this->headersRead = true;
                $this->headersLine = $this->readingLine();
            }

            if ($readingLine > 0) {
                $this->seekingLine(
                    $readingLine <= $this->headersLine
                        ? $this->headersLine + 1 : $readingLine + 1
                );
            }
        }
        return $this->headers;
    }

    /**
     * @return array|null
     * @throws FileException
     */
    public function read()
    {
        $this->readHeaders();

        ++$this->readingLine;
        if ($this->openingFile->eof()) {
            return null;
        }
        if (($read = $this->openingFile->fgetcsv()) === false) {
            throw new FileException(sprintf('Could not read at line %d.', $this->readingLine()));
        }
        if ($this->skipEmpty
            && [] === array_filter($read, function ($value) {
                return !null_or_empty_string($value);
            })) {
            return $this->read();
        }
        if ($this->withHeaders) {
            $headers = is_array($this->withHeaders) ? $this->withHeaders : ($this->headers ?? []);
            $readWithHeaders = [];
            foreach ($headers as $header) {
                $readWithHeaders[$header] = array_shift($read);
            }
            foreach ($read as $item) {
                $readWithHeaders[] = $item;
            }
            return $readWithHeaders;
        }
        return $read;
    }

    public function close(): static
    {
        $this->headersRead = false;
        $this->withHeaders = false;
        $this->headers = null;
        return parent::close();
    }
}
