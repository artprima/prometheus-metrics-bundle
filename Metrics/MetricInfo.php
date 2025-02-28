<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler\MetricInfoResolverCompilerPass;

/**
 * MetricInfo it's a model that defines the way of recording labels for the metrics.
 * Combined with MetricInfoResolverInterface can be extended to provide custom labels.
 *
 * @see MetricInfoResolverInterface
 * @see MetricInfoResolverCompilerPass
 */
class MetricInfo
{
    private string $requestMethod;
    private string $requestRoute;

    public function __construct(string $requestMethod, string $requestRoute)
    {
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

    /**
     * @return array<string>
     */
    public function getLabels(): array
    {
        return [sprintf('%s-%s', $this->requestMethod, $this->requestRoute)];
    }
}
