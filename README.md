| [Master][Master] |
|:----------------:|
| [![Build Status][Master image]][Master] |
| [![Coverage Status][Master coverage image]][Master coverage] |
| [![Quality Status][Master quality image]][Master quality] |

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

If you want to collect your own metrics, you should create a class that will implement `Artprima\PrometheusMetricsBundle\Metrics\MetricsGeneratorInterface`.
Then declare it this way:

```yaml
    App\Metrics\MyMetricsGenerator:
        tags:
            - { name: prometheus_metrics_bundle.metrics_generator }
```

NB: do NOT add a call to `init()` as it will be done automatically by the relevant compiler pass.

Code license
============

You are free to use the code in this repository under the terms of the MIT license. LICENSE contains a copy of this license.

  [Master image]: https://travis-ci.org/artprima/prometheus-metrics-bundle.svg?branch=master
  [Master]: https://travis-ci.org/artprima/prometheus-metrics-bundle
  [Master coverage image]: https://img.shields.io/scrutinizer/coverage/g/artprima/prometheus-metrics-bundle/master.svg?style=flat-square
  [Master coverage]: https://scrutinizer-ci.com/g/artprima/prometheus-metrics-bundle/?branch=master
  [Master quality image]: https://img.shields.io/scrutinizer/g/artprima/prometheus-metrics-bundle/master.svg
  [Master quality]: https://scrutinizer-ci.com/g/artprima/prometheus-metrics-bundle/?branch=master
