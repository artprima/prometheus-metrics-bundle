<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\StorageFactory;

use Prometheus\Storage\Adapter;

class FactoryRegistry
{
    /**
     * @var StorageFactoryInterface[]
     */
    private $factories;

    /**
     * @param StorageFactoryInterface[] $factories
     */
    public function __construct(array $factories = [])
    {
        foreach ($factories as $factory) {
            $this->register($factory);
        }
    }

    /**
     * Register a custom factory.
     */
    public function register(StorageFactoryInterface $factory): void
    {
        $this->factories[$factory->getName()] = $factory;
    }

    /**
     * Gets the registered factory by the name and create the adapter.
     */
    public function create(array $options): Adapter
    {
        if (isset($options['url'])) {
            $options = array_merge($options, $this->parseDsn($options['url']));
            $options['type'] = $options['scheme'];
            unset($options['url'], $options['scheme']);
        }

        if (!($name = $options['type'] ?? false) || !isset($this->factories[$name])) {
            throw new \InvalidArgumentException('The scheme of the adapter is not defined. Could not find factory for "'.$name.'"');
        }

        return $this->factories[$name]->create($options);
    }

    /**
     * Parse the dsn string and returns the array key.
     *
     * @see parse_url()
     */
    private function parseDsn(string $dsn): array
    {
        // Only parsing dsn as url: legacy use case.
        // This will manage non url value as scheme.
        if (false === strpos($dsn, ':')) {
            return ['scheme' => $dsn];
        }

        $options = parse_url($dsn);

        if (false === $options) {
            throw new \InvalidArgumentException(sprintf('Invalid DSN %s.', $dsn));
        }

        $options = array_map('rawurldecode', $options);

        // Manage the "driver:var1=value1;var2=value2" synthax.
        if (isset($options['path']) && !isset($options['query']) && false !== strpos($options['path'], ';')) {
            $options['query'] = str_replace(';', '&', $options['path']);
            unset($options['path']);
        }

        // Parse the query string as additionnal options.
        if (isset($options['query'])) {
            $query = [];
            parse_str($options['query'], $query);
            $options = array_merge($options, $query);
            unset($options['query']);
        }

        if (isset($options['path'])) {
            $options['path'] = trim((string) $options['path'], '/');
        }

        if (isset($options['port'])) {
            $options['port'] = (int) $options['port'];
        }

        return $options;
    }
}
