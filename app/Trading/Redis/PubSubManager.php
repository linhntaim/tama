<?php

namespace App\Trading\Redis;

use Illuminate\Redis\Connectors\PhpRedisConnector;
use Illuminate\Redis\Connectors\PredisConnector;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

class PubSubManager
{
    public function create(LoopInterface $loop, ?string $name = null): PromiseInterface
    {
        $name = $name ?: 'default';
        $config = config('database.redis');
        $options = $config['options'] ?? [];

        if (isset($config[$name])) {
            return $this->createConnector($loop, $this->mergeConfig($config['client'], $config[$name], $options));
        }

        if (isset($config['clusters'][$name])) {
            throw new InvalidArgumentException("Redis cluster connection [$name] not supported.");
        }

        throw new InvalidArgumentException("Redis connection [$name] not configured.");
    }

    protected function mergeConfig($client, array $config, array $options): array
    {
        return match ($client) {
            /**
             * @see PredisConnector
             */
            'predis' => array_merge(
                $config,
                $options,
                Arr::pull($config, 'options', []),
                isset($config['prefix']) ? ['prefix' => $config['prefix']] : []
            ),
            /**
             * @see PhpRedisConnector
             */
            default => array_merge(
                $config,
                $options,
                Arr::pull($config, 'options', [])
            )
        };
    }

    protected function createConnector(LoopInterface $loop, array $config): PromiseInterface
    {
        $uri = $config['url'] ?? sprintf('%s:%s', $config['host'] ?? '127.0.0.1', $config['port'] ?? '6379');
        $prefix = $config['prefix'] ?? '';
        $database = $config['database'] ?? '0';

        return (new PubSubConnector($loop))
            ->connect($uri)
            ->then(function (PubSubConnection $connection) use ($prefix, $database) {
                return $connection
                    ->setPrefix($prefix)
                    ->select($database);
            });
    }
}
