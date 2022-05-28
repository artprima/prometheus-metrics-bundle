|                    [Master][Master Link]                     |
|:------------------------------------------------------------:|
|           [![Build Status][Master image]][Master]            |
| [![Coverage Status][Master coverage image]][Master coverage] |


Symfony 5 and 6 Prometheus Metrics Bundle
=========================================

A Symfony bundle for the `promphp/prometheus_client_php`.

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
        $bundles = [
            // ...
            new Artprima\PrometheusMetricsBundle\ArtprimaPrometheusMetricsBundle(),
        ];

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

    # ignoring some routes in metrics
    ignored_routes: [some_route_name, another_route_name]

    # metrics backend
    storage:
        # DSN of the storage. All parsed values will override explicitly set parameters. Ex: redis://127.0.0.1?timeout=0.1
        url: ~
        
        # Known values: in_memory, apcu, apcng, redis
        type: in_memory
        
        # Available parameters used by redis
        host: 127.0.0.1
        port: 6379
        timeout: 0.1
        read_timeout: 10
        persistent_connections: false
        password: ~
        database: ~ # Int value used by redis adapter
        prefix: ~   # String value used by redis and apcu

        # A variable parameter to define additionnal options as key / value.
        options:
            foo: bar

    # used to disable default application metrics
    disable_default_metrics: false

    # used to disable default metrics from promphp/prometheus_client_php
    disable_default_promphp_metrics: false

    # used to enable console metrics
    enable_console_metrics: false
```

Supported types are:
| Adapter name | Prometheus class            |
|--------------|-----------------------------|
| in_memory    | Prometheus\Storage\InMemory |
| apcu         | Prometheus\Storage\APC      |
| apcung       | Prometheus\Storage\APCng    |
| redis        | Prometheus\Storage\Redis    |


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

Custom Metrics Collector
========================

If you want to collect your own metrics, you should create a class that will implement one or several interfaces that are
the children of the `Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorInterface`.

```php
<?php

declare(strict_types=1);

namespace App\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\RequestMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\TerminateMetricsCollectorInterface;
use Prometheus\CollectorRegistry;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

/**
 * Class MyMetricsCollector.
 */
class MyMetricsCollector implements RequestMetricsCollectorInterface, TerminateMetricsCollectorInterface
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

When using autoconfigure = true, by implementing `Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorInterface`
Symfony will automatically configure your metrics collector to be used by the collector registry.

By implementing one of the following interfaces you can collect the metrics on one of the listed Symfony kernel events:

- `Artprima\PrometheusMetricsBundle\Metrics\PreRequestMetricsCollectorInterface`
  - collect metrics on "kernel.request" event with a priority of 1024.
- `Artprima\PrometheusMetricsBundle\Metrics\RequestMetricsCollectorInterface`
  - collect metrics on "kernel.request" event (default priority).
- `Artprima\PrometheusMetricsBundle\Metrics\PreExceptionMetricsCollectorInterface`
  - collect metrics on "kernel.exception" event with a priority of 1024.
- `Artprima\PrometheusMetricsBundle\Metrics\ExceptionMetricsCollectorInterface`
  - collect metrics on "kernel.exception" event with (default priority).
- `Artprima\PrometheusMetricsBundle\Metrics\TerminateMetricsCollectorInterface`
  - collect metrics on "kernel.terminate" event.
  
The following collectors will only work if you define `enable_console_metrics: true` in the bundle configuration:

- `Artprima\PrometheusMetricsBundle\Metrics\ConsoleCommandMetricsCollectorInterface`
  - collect metrics on "console.command" event.
- `Artprima\PrometheusMetricsBundle\Metrics\ConsoleTerminateMetricsCollectorInterface`
  - collect metrics on "console.terminate" event.
- `Artprima\PrometheusMetricsBundle\Metrics\ConsoleErrorMetricsCollectorInterface`
  - collect metrics on "console.error" event.

For advanced usage you can implement `Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorInterface` directly.

There is also `Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorInitTrait` will add the `init` method to your
collector.

If you don't use autoconfigure = true, then you will have to add this to your `services.yaml`:

```yaml
    App\Metrics\MyMetricsCollector:
        tags:
            - { name: prometheus_metrics_bundle.metrics_generator }
```

Custom Storage Adapter Factory
========================

A storage adapter is an instance of `Prometheus\Storage\Adapter`. 
To create your own storage adapter you should create a custom factory implementing `Artprima\PrometheusMetricsBundle\StorageFactory\StorageFactoryInterface`.

```php
<?php

declare(strict_types=1);

namespace App\Metrics;

use Artprima\PrometheusMetricsBundle\StorageFactory\StorageFactoryInterface;
use Prometheus\Storage\Adapter;

class DummyFactory implements StorageFactoryInterface
{
    public function getName(): string
    {
        return 'dummy';
    }

    public function create(array $options): Adapter
    {
        return new Dummy($options);
    }
}
```

Symfony will automatically configure your storage factory with autoconfigure = true and implementing `Artprima\PrometheusMetricsBundle\StorageFactory\StorageFactoryInterface`.
If you don't use autoconfigure = true, then you will have to add this to your `services.yaml`:

```yaml
    App\Metrics\DummyFactory:
        tags:
            - { name: prometheus_metrics_bundle.adapter_factory }
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
Instance name comes from the server var `HOSTNAME` (`$request->server->get('HOSTNAME')`) and defaults to `dev`.

Clear Metrics
=============

The bundle provides a console command to clear metrics from the storage. Simply run:
```bash
./bin/console artprima:prometheus:metrics:clear
```

Code license
============

You are free to use the code in this repository under the terms of the MIT license. LICENSE contains a copy of this license.

  [Master Link]: https://github.com/artprima/prometheus-metrics-bundle/tree/master
  [Master image]: https://github.com/artprima/prometheus-metrics-bundle/workflows/PHP/badge.svg?branch=master
  [Master]: https://github.com/artprima/prometheus-metrics-bundle/actions?query=workflow%3APHP+branch%3Amaster
  [Master coverage image]: https://img.shields.io/codecov/c/github/artprima/prometheus-metrics-bundle/master.svg
  [Master coverage]: https://codecov.io/gh/artprima/prometheus-metrics-bundle
