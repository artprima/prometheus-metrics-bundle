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
    private array $additionalLabels;

    public function __construct(string $requestMethod, string $requestRoute, array $additionalLabels = [])
    {
        $this->requestMethod = $requestMethod;
        $this->requestRoute = $requestRoute;
        $this->additionalLabels = $additionalLabels;
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
     * Additional labels that can be used in the metrics.
     * For example: ['color' => 'blue'].
     *
     * @return array<string>
     */
    public function getAdditionalLabels(): array
    {
        return $this->additionalLabels;
    }

    /**
     * Return additional labels values.
     *
     * @return array<string>
     */
    public function getAdditionalLabelsValues(): array
    {
        return array_values($this->additionalLabels);
    }

    /**
     * Will return: ['action', 'color'].
     *
     * @return array<string>
     */
    public function getLabelNames(): array
    {
        return array_merge(['action'], array_keys($this->getAdditionalLabels()));
    }

    /**
     * Will return: ['GET-/api/v1/users', 'blue'].
     *
     * @return array<string>
     */
    public function getLabelValues(): array
    {
        $values = [sprintf('%s-%s', $this->requestMethod, $this->requestRoute)];

        return array_merge($values, $this->getAdditionalLabelsValues());
    }

    /**
     * Used when you want to increment the metric for all labels.
     * Default example is ['all'].
     *
     * @return array<string>
     */
    public function getLabelValueForAll(): array
    {
        return ['all'];
    }
}
