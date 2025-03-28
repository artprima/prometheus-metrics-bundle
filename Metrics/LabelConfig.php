<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Prometheus\Collector;

class LabelConfig
{
    public const REQUEST_ATTRIBUTE = 'request_attribute';
    public const REQUEST_HEADER = 'request_header';

    public function __construct(
        public string $name,
        public string $type,
        public string $value,
    ) {
        // Throws an exception if the label name is invalid according to the Prometheus specification.
        Collector::assertValidLabel($name);
    }
}
