<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Tests\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\LabelConfig;
use Artprima\PrometheusMetricsBundle\Metrics\LabelResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class LabelResolverTest extends TestCase
{
    private LabelResolver $labelResolver;

    protected function setUp(): void
    {
        $this->labelResolver = new LabelResolver();
    }

    public function testGetLabelNamesWithEmptyConfig(): void
    {
        $this->assertEquals([], $this->labelResolver->getLabelNames());
    }

    public function testGetLabelNames(): void
    {
        $labelConfigs = [
            ['name' => 'owner', 'type' => LabelConfig::REQUEST_ATTRIBUTE, 'value' => '_owner'],
            ['name' => 'team', 'type' => LabelConfig::REQUEST_ATTRIBUTE, 'value' => '_team'],
            ['name' => 'client', 'type' => LabelConfig::REQUEST_HEADER, 'value' => 'X-Client-Name'],
        ];

        $this->labelResolver->setLabelConfigs($labelConfigs);

        $expectedNames = ['owner', 'team', 'client'];
        $this->assertEquals($expectedNames, $this->labelResolver->getLabelNames());
    }

    public function testResolveLabelsWithRequestAttributes(): void
    {
        $labelConfigs = [
            ['name' => 'owner', 'type' => LabelConfig::REQUEST_ATTRIBUTE, 'value' => '_owner'],
            ['name' => 'team', 'type' => LabelConfig::REQUEST_ATTRIBUTE, 'value' => '_team'],
        ];

        $this->labelResolver->setLabelConfigs($labelConfigs);

        $request = new Request();
        $request->attributes->set('_owner', 'users-squad');
        $request->attributes->set('_team', 'team-123');

        $expectedLabels = [
            'owner' => 'users-squad',
            'team' => 'team-123',
        ];

        $this->assertEquals($expectedLabels, $this->labelResolver->resolveLabels($request));
    }

    public function testResolveLabelsWithRequestHeaders(): void
    {
        $labelConfigs = [
            ['name' => 'client', 'type' => LabelConfig::REQUEST_HEADER, 'value' => 'X-Client-Name'],
            ['name' => 'version', 'type' => LabelConfig::REQUEST_HEADER, 'value' => 'X-App-Version'],
        ];

        $this->labelResolver->setLabelConfigs($labelConfigs);

        $request = new Request();
        $request->headers->set('X-Client-Name', 'mobile-app');
        $request->headers->set('X-App-Version', '1.2.3');

        $expectedLabels = [
            'client' => 'mobile-app',
            'version' => '1.2.3',
        ];

        $this->assertEquals($expectedLabels, $this->labelResolver->resolveLabels($request));
    }

    public function testResolveLabelsWithMissingValuesInRequest(): void
    {
        $labelConfigs = [
            ['name' => 'owner', 'type' => LabelConfig::REQUEST_ATTRIBUTE, 'value' => '_owner'],
            ['name' => 'team', 'type' => LabelConfig::REQUEST_ATTRIBUTE, 'value' => '_team'],
        ];

        $this->labelResolver->setLabelConfigs($labelConfigs);

        // We only set the _owner and not the _team, _team should be empty string.
        $request = new Request();
        $request->attributes->set('_owner', 'users-team');

        $expectedLabels = [
            'owner' => 'users-team',
            'team' => '',
        ];

        $this->assertEquals($expectedLabels, $this->labelResolver->resolveLabels($request));
    }
}
