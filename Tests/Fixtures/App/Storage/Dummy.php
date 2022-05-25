<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\Fixtures\App\Storage;

use Prometheus\Storage\Adapter;

class Dummy implements Adapter
{
    public $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function collect(): array
    {
        return [];
    }

    public function updateSummary(array $data): void
    {
    }

    public function updateHistogram(array $data): void
    {
    }

    public function updateGauge(array $data): void
    {
    }

    public function updateCounter(array $data): void
    {
    }

    public function wipeStorage(): void
    {
    }
}
