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
    private array $additionalLabelValues;

    public function __construct(string $requestMethod, string $requestRoute, array $additionalLabelValues = [])
    {
        $this->requestMethod = $requestMethod;
        $this->requestRoute = $requestRoute;
        $this->additionalLabelValues = $additionalLabelValues;
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
     * Return additional labels values.
     * Example: ['color'].
     *
     * @return array<string>
     */
    public function getAdditionalLabelValues(): array
    {
        return $this->additionalLabelValues;
    }

    /**
     * Will return: ['GET-/api/v1/users', 'blue'].
     *
     * @return array<string>
     */
    public function getLabelValues(): array
    {
        $values = [sprintf('%s-%s', $this->requestMethod, $this->requestRoute)];

        return array_merge($values, $this->getAdditionalLabelValues());
    }
}
