<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\StorageFactory;

use Prometheus\Storage\Adapter;
use Prometheus\Storage\Redis;

class RedisFactory implements StorageFactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return Redis
     */
    public function create(array $options): Adapter
    {
        if (isset($options['pass'])) {
            $options['password'] = $options['pass'];
            unset($options['pass']);
        }

        if (isset($options['path'])) {
            $options['database'] = (int) $options['path'];
            unset($options['path']);
        } elseif (isset($options['database'])) {
            $options['database'] = (int) $options['database'];
        }

        if (isset($options['prefix'])) {
            Redis::setPrefix($options['prefix']);
            unset($options['prefix']);
        }

        if (isset($options['persistent_connections'])) {
            $options['persistent_connections'] = filter_var($options['persistent_connections'], FILTER_VALIDATE_BOOLEAN);
        }

        // Cast read_timeout option to avoid TypeError when working with Redis
        // see for more details: https://github.com/phpredis/phpredis/issues/1538
        if (isset($options['read_timeout'])) {
            $options['read_timeout'] = (string) $options['read_timeout'];
        }

        // The timeout option is cast to float in Redis class.
        return new Redis($options);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'redis';
    }
}
