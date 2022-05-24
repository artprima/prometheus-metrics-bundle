CHANGELOG
=========

* 1.14.0 (2022-05-??)

  * Add support for `disable_default_promphp_metrics` config option, which allows disabling
    default metrics from [promphp/prometheus_client_php](https://github.com/promphp/prometheus_client_php) (thanks to [@Johnmeurt](https://github.com/Johnmeurt)).

* 1.13.0 (2021-12-05)

  * Add support for Symfony 5.4 and 6.0.
  * Drop support for Symfony 4.4 and 5.2.
  * Add support for PHP 8.1.
  * Drop support for PHP 7.3.
  * Drop support for deprecated `prometheus_metrics_bundle.metrics_generator` service tag.
  * Drop support for deprecated `MetricsGeneratorInterface` interface.
