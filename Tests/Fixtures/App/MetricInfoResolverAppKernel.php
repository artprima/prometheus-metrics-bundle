<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Tests\Fixtures\App;

use Artprima\PrometheusMetricsBundle\Tests\Metrics\DummyMetricInfoResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tests\Artprima\PrometheusMetricsBundle\Fixtures\App\AppKernel;

class MetricInfoResolverAppKernel extends AppKernel
{
    protected function build(ContainerBuilder $container) : void
    {
        parent::build($container);

        $container
            ->register('dummy_metric_info_resolver', DummyMetricInfoResolver::class)
            ->addTag('prometheus_metrics_bundle.metric_info_resolver');
    }

}
