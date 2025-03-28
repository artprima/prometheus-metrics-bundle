<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Tests\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\LabelConfig;
use Artprima\PrometheusMetricsBundle\Metrics\LabelResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class LabelResolverTest extends TestCase
{
    public function testGetLabelNames(): void
    {
        $labelConfigs = [
            new LabelConfig('owner', LabelConfig::REQUEST_ATTRIBUTE, '_owner'),
            new LabelConfig('team', LabelConfig::REQUEST_ATTRIBUTE, '_team'),
            new LabelConfig('client', LabelConfig::REQUEST_ATTRIBUTE, 'X-Client-Name'),
        ];

        $labelResolver = new LabelResolver($labelConfigs);

        $expectedNames = ['owner', 'team', 'client'];
        $this->assertEquals($expectedNames, $labelResolver->getLabelNames());
    }

    public function testGetLabelNamesNoConfig(): void
    {
        $this->assertEquals([], (new LabelResolver())->getLabelNames());
    }

    public function testResolveLabelsWithRequestAttributes(): void
    {
        $labelConfigs = [
            new LabelConfig('owner', LabelConfig::REQUEST_ATTRIBUTE, '_owner'),
            new LabelConfig('team', LabelConfig::REQUEST_ATTRIBUTE, '_team'),
        ];

        $labelResolver = new LabelResolver($labelConfigs);

        $request = new Request();
        $request->attributes->set('_owner', 'users-squad');
        $request->attributes->set('_team', 'team-123');

        $expectedLabels = [
            'owner' => 'users-squad',
            'team' => 'team-123',
        ];

        $this->assertEquals($expectedLabels, $labelResolver->resolveLabels($request));
    }

    public function testGetLabelNamesIncludingAction(): void
    {
        $labelConfigs = [
            new LabelConfig('owner', LabelConfig::REQUEST_ATTRIBUTE, '_owner'),
            new LabelConfig('team', LabelConfig::REQUEST_ATTRIBUTE, '_team'),
        ];

        $labelResolver = new LabelResolver($labelConfigs);

        $request = new Request();
        $request->attributes->set('_owner', 'users-squad');
        $request->attributes->set('_team', 'team-123');

        $expectedLabels = [
            'action',
            'owner',
            'team',
        ];

        $this->assertEquals($expectedLabels, $labelResolver->getLabelNamesIncludingAction());
    }

    public function testGetResolvedLabelValues(): void
    {
        $labelConfigs = [
            new LabelConfig('owner', LabelConfig::REQUEST_ATTRIBUTE, '_owner'),
            new LabelConfig('team', LabelConfig::REQUEST_ATTRIBUTE, '_team'),
        ];

        $labelResolver = new LabelResolver($labelConfigs);

        $request = new Request();
        $request->attributes->set('_owner', 'users-squad');
        $request->attributes->set('_team', 'team-123');

        $expectedLabels = [
            'users-squad',
            'team-123',
        ];

        $this->assertEquals($expectedLabels, $labelResolver->getResolvedLabelValues($request));
    }

    public function testGetAllLabelValues(): void
    {
        $labelConfigs = [
            new LabelConfig('owner', LabelConfig::REQUEST_ATTRIBUTE, '_owner'),
            new LabelConfig('team', LabelConfig::REQUEST_ATTRIBUTE, '_team'),
        ];

        $labelResolver = new LabelResolver($labelConfigs);
        $expectedLabels = [
            'all',
            '',
            '',
        ];
        $this->assertEquals($expectedLabels, $labelResolver->getAllLabelValues());
    }

    public function testGetAllLabelValuesNoLabelConfig(): void
    {
        $expectedLabels = [
            'all',
        ];
        $this->assertEquals($expectedLabels, (new LabelResolver())->getAllLabelValues());
    }

    public function testResolveLabelsWithRequestHeaders(): void
    {
        $labelConfigs = [
            new LabelConfig('client', LabelConfig::REQUEST_HEADER, 'X-Client-Name'),
            new LabelConfig('version', LabelConfig::REQUEST_HEADER, 'X-App-Version'),
        ];

        $labelResolver = new LabelResolver($labelConfigs);

        $request = new Request();
        $request->headers->set('X-Client-Name', 'mobile-app');
        $request->headers->set('X-App-Version', '1.2.3');

        $expectedLabels = [
            'client' => 'mobile-app',
            'version' => '1.2.3',
        ];

        $this->assertEquals($expectedLabels, $labelResolver->resolveLabels($request));
    }

    public function testResolveLabelsWithMissingValuesInRequest(): void
    {
        $labelConfigs = [
            new LabelConfig('owner', LabelConfig::REQUEST_ATTRIBUTE, '_owner'),
            new LabelConfig('team', LabelConfig::REQUEST_ATTRIBUTE, '_team'),
        ];

        $labelResolver = new LabelResolver($labelConfigs);

        // We only set the _owner and not the _team, _team should be empty string.
        $request = new Request();
        $request->attributes->set('_owner', 'users-team');

        $expectedLabels = [
            'owner' => 'users-team',
            'team' => '',
        ];

        $this->assertEquals($expectedLabels, $labelResolver->resolveLabels($request));
    }
}
