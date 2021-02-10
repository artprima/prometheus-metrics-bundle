<?php

declare(strict_types=1);

namespace App\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorInitTrait;
use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorInterface;
use Prometheus\Exception\MetricNotFoundException;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

// this class should be autoconfigured because of MetricsCollectorInterface
class CustomMetricsCollector implements MetricsCollectorInterface
{
    use MetricsCollectorInitTrait;

    public function collectStart(RequestEvent $event): void
    {
    }

    public function collectRequest(RequestEvent $event): void
    {
        $this->setAppVersion('1.2.3');
    }

    public function collectResponse(TerminateEvent $event): void
    {
    }

    private function setAppVersion(string $value): void
    {
        $name = 'app_version';
        try {
            // the trick with try/catch let's us setting the instance name only once
            $this->collectionRegistry->getGauge($this->namespace, $name);
        } catch (MetricNotFoundException $e) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $gauge = $this->collectionRegistry->registerGauge(
                $this->namespace,
                $name,
                'app version',
                ['version']
            );
            $gauge->set(1, [$value]);
        }
    }
}
