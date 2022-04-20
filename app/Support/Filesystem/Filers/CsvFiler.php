<?php

namespace App\Support\Filesystem\Filers;

use App\Support\Exceptions\FileException;
use SplFileObject;

class CsvFiler extends Filer
{
    public static function create(?string $in = null, ?string $name = null, ?string $extension = null): static
    {
        return parent::create($in, $name, 'csv');
    }

    protected bool $skipEmpty = true;

    public function setControl(string $separator = ',', string $enclosure = '"', string $escape = '\\'): static
    {
        $this->openingFile->setCsvControl($separator, $enclosure, $escape);
        return $this;
    }

    public function open($mode): static
    {
        return take(parent::open($mode), function () {
            if ($this->skipEmpty) {
                $this->openingFile->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
            }
        });
    }

    /**
     * @param array $data
     * @return static
     * @throws FileException
     */
    public function write($data): static
    {
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

    /**
     * @param array $data
     * @param bool $close
     * @return $this
     * @throws FileException
     */
    public function writeAll($data, bool $close = true): static
    {
        foreach ($data as $item) {
            $this->write($item);
        }
        $close && $this->close();
        return $this;
    }

    /**
     * @throws FileException
     */
    public function read(): ?array
    {
        if ($this->openingFile->eof()) {
            return null;
        }
        if (($read = $this->openingFile->fgetcsv()) === false) {
            throw new FileException('Could not read CSV line.');
        }
        if ($this->skipEmpty
            && [] === array_filter($read, function ($value) {
                return !null_or_empty_string($value);
            })) {
            return $this->read();
        }
        return $read;
    }

    public function readAll(bool $close = true): array
    {
        $all = [];
        while (!is_null($read = $this->read())) {
            $all[] = $read;
        }
        $close && $this->close();
        return $all;
    }
}