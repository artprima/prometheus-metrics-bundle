<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArtprimaPrometheusBundleTest extends WebTestCase
{
    public function testSearchProductsActionSuccess()
    {
        $client = static::createClient();

        $client->request('GET', '/metrics/prometheus');
        self::assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        echo $content;
        $expected = "# HELP php_info Information about the PHP environment.\n# TYPE php_info gauge\nphp_info{version=\"%s\"} 1";
        self::assertContains(
            sprintf($expected, PHP_VERSION),
            trim($content)
        );
    }
}