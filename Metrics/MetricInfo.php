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
final class MetricInfo
{
    private string $requestMethod;
    private string $requestRoute;
    private array $additionalLabelValues;

    /**
     * Formats the action label: Example: '%s %s'.
     * The first %s will be replaced with the request method and the second %s with the request route.
     *
     * @param string $actionFormat
     */
    private string $actionFormat;

    public function __construct(string $actionFormat, string $requestMethod, string $requestRoute, array $additionalLabelValues = [])
    {
        $this->actionFormat = $actionFormat;
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
     * Return additional labels values. Example: ['red'].
     *
     * @return array<string>
     */
    public function getAdditionalLabelValues(): array
    {
        return $this->additionalLabelValues;
    }

    /**
     * Will return: ['GET-/api/v1/users'] if no additional labels are defined.
     *
     * Will return: ['GET-/api/v1/users', 'red', 'mobile-app'] if additional labels are defined as ['color', 'client_name'].
     *
     * @return array<string>
     */
    public function getLabelValues(): array
    {
        $action = sprintf($this->actionFormat, $this->requestMethod, $this->requestRoute);

        return array_merge([$action], $this->getAdditionalLabelValues());
    }
}
