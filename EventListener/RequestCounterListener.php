<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\EventListener;

use Artprima\PrometheusMetricsBundle\Metrics\MetricsGeneratorRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * Class RequestCounterListener.
 */
class RequestCounterListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var MetricsGeneratorRegistry
     */
    private $metricsGenerators;

    /**
     * @var array
     */
    private $ignoredRoutes;

    public function __construct(MetricsGeneratorRegistry $metricsGenerators, array $ignoredRoutes = ['prometheus_bundle_prometheus'])
    {
        $this->metricsGenerators = $metricsGenerators;
        $this->ignoredRoutes = $ignoredRoutes;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $requestRoute = $event->getRequest()->attributes->get('_route');
        if (in_array($requestRoute, $this->ignoredRoutes, true)) {
            return;
        }

        foreach ($this->metricsGenerators->getMetricsGenerators() as $generator) {
            try {
                $generator->collectRequest($event);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error(
                        $e->getMessage(),
                        ['from' => 'request_collector', 'class' => get_class($generator)]
                    );
                }
            }
        }
    }

    public function onKernelTerminate(PostResponseEvent $event): void
    {
        $requestRoute = $event->getRequest()->attributes->get('_route');
        if (in_array($requestRoute, $this->ignoredRoutes, true)) {
            return;
        }

        foreach ($this->metricsGenerators->getMetricsGenerators() as $generator) {
            try {
                $generator->collectResponse($event);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error(
                        $e->getMessage(),
                        ['from' => 'response_collector', 'class' => get_class($generator)]
                    );
                }
            }
        }
    }
}
