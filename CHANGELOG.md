CHANGELOG
=========

* 1.20.0 (2025-04-09)

  * This release introduces an official support for php 8.4 and Symfony 7.2 (thanks to [@mickverm](https://github.com/mickverm))
  * Drop php 8.0 and update the code to php 8.1 features ([#108](https://github.com/artprima/prometheus-metrics-bundle/pull/108))

* 1.19.0 (2023-12-04)

  * This release introduces an official support for php 8.3 and Symfony 6.4/7.0 (thanks to [@rmbl](https://github.com/rmbl))
  * Add php 8.3 to GitHub Actions workflows ([#92](https://github.com/artprima/prometheus-metrics-bundle/pull/92))
  * Add symfony 6.4 to GitHub workflow ([#93](https://github.com/artprima/prometheus-metrics-bundle/issues/93))
  * Add symfony 7.0 to GitHub workflow ([#94](https://github.com/artprima/prometheus-metrics-bundle/issues/94))

* 1.18.0 (2023-08-13)

  * Improve compatibility with Symfony 6.3 and PHPUnit 10 ([#88](https://github.com/artprima/prometheus-metrics-bundle/pull/91)) (thanks to [@Johnmeurt](https://github.com/Johnmeurt))

* 1.17.0 (2023-06-04)

  * Add support for Symfony 6.3
  * Update dev and testing dependencies
  * Add lazy load command description for `artprima:prometheus:metrics:clear` ([#86](https://github.com/artprima/prometheus-metrics-bundle/pull/86)) (thanks to [@marein](https://github.com/marein))

* 1.16.0 (2023-02-22)

  * Remove deprecations, update supported versions ([#84](https://github.com/artprima/prometheus-metrics-bundle/commit/3a7d96791d83e63c98366411f8ab7654517cbe61))
  * Fix deprecation in ClearMetricsCommand ([#83](https://github.com/artprima/prometheus-metrics-bundle/commit/7138e1635a09f8863c82e7644c7953a3cef38a95)) (thanks to [@alshenetsky](https://github.com/alshenetsky))

* 1.15.0 (2023-02-21)

  * Allow usage of env var for the namespace ([#81](https://github.com/artprima/prometheus-metrics-bundle/commit/c560b4d9193104b0438f3bd943fd9a8814cc69a0)) (thanks to [@Johnmeurt](https://github.com/Johnmeurt))
  * Fix supported storage types by adding 'apcng' to allowed values ([#82](https://github.com/artprima/prometheus-metrics-bundle/commit/ff351e8f7e90508924dbdc9405f3bd51da1aab8d)) (thanks to [@edditor](https://github.com/edditor))


* 1.14.0 (2022-05-28)

  * Add support for `disable_default_promphp_metrics` config option, which allows disabling
    default metrics from [promphp/prometheus_client_php](https://github.com/promphp/prometheus_client_php) ([#63](https://github.com/artprima/prometheus-metrics-bundle/pull/63)) (thanks to [@Johnmeurt](https://github.com/Johnmeurt)).
  * Add console command to clear metrics ([#67](https://github.com/artprima/prometheus-metrics-bundle/pull/67)) (thanks to [@Johnmeurt](https://github.com/Johnmeurt)).
  * Add storage adapter factory ([#68](https://github.com/artprima/prometheus-metrics-bundle/pull/68)) (thanks to [@Johnmeurt](https://github.com/Johnmeurt)).
  * Add APCng adapter ([#71](https://github.com/artprima/prometheus-metrics-bundle/pull/71)) (thanks to [@Johnmeurt](https://github.com/Johnmeurt)).
  * Drop support for Symfony 5.3.


* 1.13.0 (2021-12-05)

  * Add support for Symfony 5.4 and 6.0.
  * Drop support for Symfony 4.4 and 5.2.
  * Add support for PHP 8.1.
  * Drop support for PHP 7.3.
  * Drop support for deprecated `prometheus_metrics_bundle.metrics_generator` service tag.
  * Drop support for deprecated `MetricsGeneratorInterface` interface.
