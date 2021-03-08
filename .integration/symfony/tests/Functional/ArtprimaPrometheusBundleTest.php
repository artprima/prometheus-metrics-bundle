<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArtprimaPrometheusBundleTest extends WebTestCase
{
    public function testHomepageMetrics()
    {
        $client = static::createClient();
        $client->disableReboot();

        $client->request('GET', '/');

        $client->request('GET', '/metrics/prometheus');
        self::assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        echo $content;
        $expected = "# HELP php_info Information about the PHP environment.\n# TYPE php_info gauge\nphp_info{version=\"%s\"} 1";
        self::assertContains(
            sprintf($expected, PHP_VERSION),
            trim($content)
        );
        self::assertContains(
            'symfony_http_2xx_responses_total{action="GET-app_dummy_homepage"} 1'.PHP_EOL,
            $content
        );
        self::assertContains(
            'symfony_http_2xx_responses_total{action="all"} 1'.PHP_EOL,
            $content
        );
        self::assertContains(
            'symfony_http_requests_total{action="GET-app_dummy_homepage"} 1'.PHP_EOL,
            $content
        );
        self::assertContains(
            'symfony_http_requests_total{action="all"} 1'.PHP_EOL,
            $content
        );
        self::assertContains(
            'symfony_instance_name{instance="dev"} 1'.PHP_EOL,
            $content
        );
        self::assertContains(
            'symfony_app_version{version="1.2.3"} 1'.PHP_EOL,
            $content
        );
    }

    public function testExceptionMetrics()
    {
        $client = static::createClient();
        $client->disableReboot();

        $client->request('GET', '/exception');

        $client->request('GET', '/metrics/prometheus');
        self::assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        echo $content;
        self::assertContains(
            'symfony_exception{class="RuntimeException"} 1'.PHP_EOL,
            $content
        );
    }
}
