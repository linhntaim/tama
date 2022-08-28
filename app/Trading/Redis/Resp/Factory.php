<?php

namespace App\Trading\Redis\Resp;

use RuntimeException;

class Factory
{
    protected string $data;

    public function create(string $data): ?RespData
    {
        $this->data = $data;
        return $this->parse();
    }

    protected function read(): ?string
    {
        return ($pos = mb_strpos($this->data, RespData::CRLF)) !== false
            ? tap(substr($this->data, 0, $pos), fn() => $this->data = substr($this->data, $pos + 2))
            : null;
    }

    protected function parse(): ?RespData
    {
        if (is_null($read = $this->read())) {
            return null;
        }

        switch ($read[0]) {
            case '+': // see https://redis.io/docs/reference/protocol-spec/#resp-simple-strings
                return new RespSimpleString(substr($read, 1));
            case '-': // see https://redis.io/docs/reference/protocol-spec/#resp-errors
                return new RespError(substr($read, 1));
            case ':': // see https://redis.io/docs/reference/protocol-spec/#resp-integers
                return new RespInteger(substr($read, 1));
            case '$': // see https://redis.io/docs/reference/protocol-spec/#resp-bulk-strings
                $length = (int)substr($read, 1);
                return match (true) {
                    $length < 0 => null,
                    default => new RespBulkString(substr($this->read(), 0, $length))
                };
            case '*': // see https://redis.io/docs/reference/protocol-spec/#resp-arrays
                $count = (int)substr($read, 1);
                switch (true) {
                    case $count < 0:
                        return null;
                    case $count === 0:
                        return new RespArray([]);
                    default:
                        $data = [];
                        while (--$count >= 0) {
                            $data[] = $this->parse();
                        }
                        return new RespArray($data);
                }
            default:
                throw new RuntimeException('RESP data format is invalid.');
        }
    }
}
