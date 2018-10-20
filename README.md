Symfony 4 Prometheus Metrics Bundle
===================================

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

Custom Metrics Generator
========================

If you want to collect your own metrics, you should create a class that will implement `Artprima\PrometheusMetricsBundle\Metrics\MetricsGeneratorInterface`.
Then declare it this way:

```yaml
    App\Metrics\MyMetricsGenerator:
        tags:
            - { name: prometheus_metrics_bundle.metrics_generator }
```

NB: do NOT add a call to `init()` as it will be done automatically by the relevant compiler pass.
