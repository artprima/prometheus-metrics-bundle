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
     * @param array<LabelConfig> $labelConfigs
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
     * Returns list of label names. i.e ['color', 'client_name'].
     *
     * @return array<string>
     */
    public function getLabelNames(): array
    {
        return $this->labelNames;
    }

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
}
