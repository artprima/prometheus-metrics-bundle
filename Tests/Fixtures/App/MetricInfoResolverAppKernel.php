<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\Fixtures\App;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tests\Artprima\PrometheusMetricsBundle\Metrics\DummyMetricInfoResolver;

class MetricInfoResolverAppKernel extends AppKernel
{
    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container
            ->register('dummy_metric_info_resolver', DummyMetricInfoResolver::class)
            ->addTag('prometheus_metrics_bundle.metric_info_resolver');
    }
}
