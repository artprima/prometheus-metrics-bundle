<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\DependencyInjection;

use Artprima\PrometheusMetricsBundle\ArtprimaPrometheusMetricsBundle;
use Artprima\PrometheusMetricsBundle\EventListener\MetricsCollectorListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServiceConfigurationTest extends TestCase
{
    private function getMetricsCollectorListenerTags(): array
    {
        $container = new ContainerBuilder();
        $bundle = new ArtprimaPrometheusMetricsBundle();
        $bundle->build($container);
        $bundle->getContainerExtension()->load([
            [
                'namespace' => 'test',
                'type' => 'in_memory',
                'storage' => ['type' => 'in_memory'],
                'ignored_routes' => ['prometheus_bundle_prometheus'],
                'disable_default_metrics' => false,
                'disable_default_promphp_metrics' => false,
                'enable_console_metrics' => false,
                'labels' => [],
            ],
        ], $container);

        // Check the service definition before compilation (when the service is still available)
        $listenerDefinition = $container->getDefinition(MetricsCollectorListener::class);

        return $listenerDefinition->getTags();
    }

    public function testMetricsCollectorListenerHasCorrectKernelTerminatePriority(): void
    {
        $tags = $this->getMetricsCollectorListenerTags();

        // Find the kernel.terminate tag
        $kernelTerminateTag = null;
        foreach ($tags['kernel.event_listener'] ?? [] as $tag) {
            if (($tag['event'] ?? null) === 'kernel.terminate') {
                $kernelTerminateTag = $tag;
                break;
            }
        }

        self::assertNotNull($kernelTerminateTag, 'kernel.terminate event listener tag should exist');
        self::assertArrayHasKey('priority', $kernelTerminateTag, 'kernel.terminate event listener should have a priority');
        self::assertEquals(1024, $kernelTerminateTag['priority'], 'kernel.terminate event listener should have priority 1024');
    }

    public function testMetricsCollectorListenerEventPriorities(): void
    {
        $tags = $this->getMetricsCollectorListenerTags();

        $expectedEventPriorities = [
            ['event' => 'kernel.request', 'method' => 'onKernelRequestPre', 'priority' => 1024],
            ['event' => 'kernel.exception', 'method' => 'onKernelException', 'priority' => 1024],
            ['event' => 'kernel.terminate', 'priority' => 1024],
        ];

        foreach ($expectedEventPriorities as $expectedTag) {
            $found = false;
            foreach ($tags['kernel.event_listener'] ?? [] as $tag) {
                if (($tag['event'] ?? null) === $expectedTag['event']) {
                    if (isset($expectedTag['method']) && ($tag['method'] ?? null) !== $expectedTag['method']) {
                        continue;
                    }
                    if (!isset($expectedTag['method']) && isset($tag['method'])) {
                        continue;
                    }

                    self::assertEquals(
                        $expectedTag['priority'],
                        $tag['priority'] ?? null,
                        sprintf(
                            'Event %s%s should have priority %d',
                            $expectedTag['event'],
                            isset($expectedTag['method']) ? ' (method: '.$expectedTag['method'].')' : '',
                            $expectedTag['priority']
                        )
                    );
                    $found = true;
                    break;
                }
            }

            self::assertTrue($found, sprintf(
                'Event %s%s should be registered',
                $expectedTag['event'],
                isset($expectedTag['method']) ? ' (method: '.$expectedTag['method'].')' : ''
            ));
        }
    }
}
