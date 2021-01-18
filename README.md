| [Master][Master Link] | [Develop][Develop Link] |
|:----------------:|:----------------:|
| [![Build Status][Master image]][Master] | [![Build Status][Develop image]][Develop] |
| [![Coverage Status][Master coverage image]][Master coverage] | [![Coverage Status][Develop coverage image]][Develop coverage] |


Symfony 5 Prometheus Metrics Bundle
=====================================

Installation
============

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require artprima/prometheus-metrics-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require artprima/prometheus-metrics-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Artprima\PrometheusMetricsBundle\ArtprimaPrometheusMetricsBundle(),
        );

        // ...
    }

    // ...
}
```

Configuration
=============

`config.yaml`

```yaml
artprima_prometheus_metrics:
    # namespace is used to prefix the prometheus metrics
    namespace: myapp

    # metrics backend type
    type: in_memory # possible values: in_memory, apcu, redis

    # ignoring some routes in metrics
    ignored_routes: [some_route_name, another_route_name]

    # used in case of type = "redis"
    redis:
        host: 127.0.0.1
        port: 6379
        timeout: 0.1
        read_timeout: 10
        persistent_connections: false
        password: ~
```

`routes.yaml`

```yaml
# expose /metrics/prometheus in your application
app_metrics:
    resource: '@ArtprimaPrometheusMetricsBundle/Resources/config/routing.xml'
```

You can alternatively define your own path and rules:

```yaml
app_metrics:
    path: /mypath/mymetrics
    controller: Artprima\PrometheusMetricsBundle\Controller\MetricsController::prometheus
```

Now your metrics are available to Prometheus using http://<yourapp_url>/metrics/prometheus.

Custom Metrics Generator
========================

If you want to collect your own metrics, you should create a class that will implement `Artprima\PrometheusMetricsBundle\Metrics\MetricsGeneratorInterface`. Something like this:

```php
<?php

declare(strict_types=1);

namespace App\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\MetricsGeneratorInterface;
use Prometheus\CollectorRegistry;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

/**
 * Class MyMetricsGenerator.
 */
class MyMetricsGenerator implements MetricsGeneratorInterface
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var CollectorRegistry
     */
    private $collectionRegistry;

    public function init(string $namespace, CollectorRegistry $collectionRegistry): void
    {
        $this->namespace = $namespace;
        $this->collectionRegistry = $collectionRegistry;
    }

    private function incRequestsTotal(?string $method = null, ?string $route = null): void
    {
        $counter = $this->collectionRegistry->getOrRegisterCounter(
            $this->namespace,
            'http_requests_total',
            'total request count',
            ['action']
        );

        $counter->inc(['all']);

        if (null !== $method && null !== $route) {
            $counter->inc([sprintf('%s-%s', $method, $route)]);
        }
    }

    private function incResponsesTotal(?string $method = null, ?string $route = null): void
    {
        $counter = $this->collectionRegistry->getOrRegisterCounter(
            $this->namespace,
            'http_responses_total',
            'total response count',
            ['action']
        );
        $counter->inc(['all']);

        if (null !== $method && null !== $route) {
            $counter->inc([sprintf('%s-%s', $method, $route)]);
        }
    }

    // called on the `kernel.request` event
    public function collectRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $requestMethod = $request->getMethod();
        $requestRoute = $request->attributes->get('_route');

        // do not track "OPTIONS" requests
        if ('OPTIONS' === $requestMethod) {
            return;
        }

        $this->incRequestsTotal($requestMethod, $requestRoute);
    }

    // called on the `kernel.terminate` event
    public function collectResponse(TerminateEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        $requestMethod = $request->getMethod();
        $requestRoute = $request->attributes->get('_route');

        $this->incResponsesTotal($requestMethod, $requestRoute);
    }
}
```

Then declare it this way:

```yaml
    App\Metrics\MyMetricsGenerator:
        # NB: do NOT add a call to `init()` as it will be done automatically by the relevant compiler pass.
        tags:
            - { name: prometheus_metrics_bundle.metrics_generator }
```

Default Metrics
===============

These are default metrics exported by the application:

```
# TYPE php_info gauge
php_info{version="7.3.25-1+ubuntu18.04.1+deb.sury.org+1"} 1
# HELP symfony_http_2xx_responses_total total 2xx response count
# TYPE symfony_http_2xx_responses_total counter
symfony_http_2xx_responses_total{action="GET-app_dummy_homepage"} 1
symfony_http_2xx_responses_total{action="all"} 1
# HELP symfony_http_requests_total total request count
# TYPE symfony_http_requests_total counter
symfony_http_requests_total{action="GET-app_dummy_homepage"} 1
symfony_http_requests_total{action="all"} 1
# HELP symfony_instance_name app instance name
# TYPE symfony_instance_name gauge
symfony_instance_name{instance="dev"} 1
```

Note that, php_info comes from the underlying library `promphp/prometheus_client_php`. Other metrics are gathered
by the built-in class `Artprima\PrometheusMetricsBundle\Metrics`. Here, in the example we have a prefix `symfony`
and the metrics show a single request to the root named `app_dummy_homepage`. Symfony instance is named `dev` here.
Instance name comes from the server var `HOSTNAME` (`$request->server->get('HOSTNAME')`) and defaults to `dev.

Code license
============

You are free to use the code in this repository under the terms of the MIT license. LICENSE contains a copy of this license.

  [Master Link]: https://github.com/artprima/prometheus-metrics-bundle/tree/master
  [Master image]: https://github.com/artprima/prometheus-metrics-bundle/workflows/PHP/badge.svg?branch=master
  [Master]: https://github.com/artprima/prometheus-metrics-bundle/actions?query=workflow%3APHP+branch%3Amaster
  [Master coverage image]: https://img.shields.io/codecov/c/github/artprima/prometheus-metrics-bundle/master.svg
  [Master coverage]: https://codecov.io/gh/artprima/prometheus-metrics-bundle

  [Develop Link]: https://github.com/artprima/prometheus-metrics-bundle/tree/develop
  [Develop image]: https://github.com/artprima/prometheus-metrics-bundle/workflows/PHP/badge.svg?branch=develop
  [Develop]: https://github.com/artprima/prometheus-metrics-bundle/actions?query=workflow%3APHP+branch%3Adevelop
  [Develop coverage image]: https://img.shields.io/codecov/c/github/artprima/prometheus-metrics-bundle/develop.svg
  [Develop coverage]: https://codecov.io/gh/artprima/prometheus-metrics-bundle/branches/develop
