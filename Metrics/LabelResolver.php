<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Symfony\Component\HttpFoundation\Request;

class LabelResolver
{
    /** @var LabelConfig[] */
    private array $labelConfigs = [];

    private array $labelNames = [];

    /**
     * Setup label configurations, based in yml configuration.
     *
     * @param array<array{name: string, type: string, value: string}> $labelConfigs
     */
    public function setLabelConfigs(array $labelConfigs): self
    {
        $this->labelConfigs = array_map(
            fn (array $config): LabelConfig => LabelConfig::createFromArray($config),
            $labelConfigs
        );

        $this->labelNames = array_map(fn (LabelConfig $config): string => $config->name, $this->labelConfigs);

        return $this;
    }

    /**
     * Resolve labels from request.
     *
     * @return array<string, string>
     */
    public function resolveLabels(Request $request): array
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
    public function getLabelNames(): array
    {
        return $this->labelNames;
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
}
