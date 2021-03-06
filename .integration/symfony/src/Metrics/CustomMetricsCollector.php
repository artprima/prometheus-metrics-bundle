<?php

declare(strict_types=1);

namespace App\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\ExceptionMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorInitTrait;
use Artprima\PrometheusMetricsBundle\Metrics\RequestMetricsCollectorInterface;
use Prometheus\Exception\MetricNotFoundException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;

// this class should be autoconfigured because of MetricsCollectorInterface
class CustomMetricsCollector implements RequestMetricsCollectorInterface, ExceptionMetricsCollectorInterface
{
    use MetricsCollectorInitTrait;

    public function collectRequest(RequestEvent $event): void
    {
        $this->setAppVersion('1.2.3');
    }

    public function collectException(ExceptionEvent $event): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $counter = $this->collectionRegistry->getOrRegisterCounter(
            $this->namespace,
            'exception',
            'app exception',
            ['class']
        );
        $counter->inc([get_class($event->getThrowable())]);
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
