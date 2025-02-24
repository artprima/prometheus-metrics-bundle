<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

class MetricInfo
{
    private string $requestMethod;
    private string $requestRoute;

    public function __construct(
        string $requestMethod,
        string $requestRoute,
    ) {
        $this->requestMethod = $requestMethod;
        $this->requestRoute = $requestRoute;
    }

    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    public function getRequestRoute(): ?string
    {
        return $this->requestRoute;
    }

    public function getLabels(): array
    {
        return [sprintf('%s-%s', $this->requestMethod, $this->requestRoute)];
    }
}
