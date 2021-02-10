<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle;

use Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler\IgnoredRoutesPass;
use Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler\RegisterMetricsCollectorPass;
use Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler\ResolveAdapterDefinitionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ArtprimaPrometheusMetricsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ResolveAdapterDefinitionPass());
        $container->addCompilerPass(new IgnoredRoutesPass());
        $container->addCompilerPass(new RegisterMetricsCollectorPass());
    }
}
