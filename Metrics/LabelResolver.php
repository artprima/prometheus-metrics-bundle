<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Symfony\Component\HttpFoundation\Request;

/**
 * LabelResolver is a single source of truth for resolving labels.
 *
 * All metrics pushed through Prometheus client must have consistent schema, and values when recording metrics.
 *
 * LabelResolver handles schema of metrics and their values in consistent way. See the public methods.
 */
final class LabelResolver
{
    /** @var LabelConfig[] */
    private array $labelConfigs = [];

    private array $labelNames = [];

    /**
     * Setup label configurations, based in yml configuration.
     *
     * @param LabelConfig[] $labelConfigs
     */
    public function __construct(array $labelConfigs = [])
    {
        $this->labelConfigs = $labelConfigs;
        $this->labelNames = array_map(fn (LabelConfig $config): string => $config->name, $this->labelConfigs);
    }

    /**
     * Return list of resolved label names, including action label i.e ['action', 'color', 'client_name'].
     *
     * @return array<string>
     */
    public function getLabelNamesIncludingAction(): array
    {
        $resolveLabels = $this->getLabelNames();

        if (empty($resolveLabels)) {
            return ['action'];
        }

        return array_merge(['action'], $this->getLabelNames());
    }

    /**
     * Return list of 'all' label values if no labels are defined. i.e ['all'].
     * If labels are defined, fill the "all" label with empty string, to match the number of labels.
     * i.e ['all', '', ''].
     *
     * @return array<string>
     */
    public function getAllLabelValues(): array
    {
        $resolveLabels = $this->getLabelNames();

        if (empty($resolveLabels)) {
            return ['all'];
        }

        // Fill the "all" label with empty string, to match the number of labels.
        return array_merge(['all'], array_fill(0, count($resolveLabels), ''));
    }

    /**
     * Return list of resolved label values. i.e ['red', 'mobile-app'].
     *
     * @return array<string>
     */
    public function getResolvedLabelValues(Request $request): array
    {
        $resolveLabels = $this->getLabelNames();

        if (empty($resolveLabels)) {
            return [];
        }

        return array_values($this->resolveLabels($request));
    }

    /**
     * Resolve labels from request.
     *
     * @return array<string, string>
     */
    private function resolveLabels(Request $request): array
    {
        $resolvedLabels = [];

        foreach ($this->labelConfigs as $labelConfig) {
            $resolvedLabels[$labelConfig->name] = match ($labelConfig->type) {
                LabelConfig::REQUEST_ATTRIBUTE => $request->attributes->has($labelConfig->value) ? (string) $request->attributes->get($labelConfig->value) : '',
                LabelConfig::REQUEST_HEADER => $request->headers->has($labelConfig->value) ? (string) $request->headers->get($labelConfig->value) : '',
                default => '',
            };
        }

        return $resolvedLabels;
    }

    /**
     * Returns list of label names defined in the yml configuration.
     * i.e ['color', 'client_name'].
     *
     * @return array<string>
     */
    private function getLabelNames(): array
    {
        return $this->labelNames;
    }
}
