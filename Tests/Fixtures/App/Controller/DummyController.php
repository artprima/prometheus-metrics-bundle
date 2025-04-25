<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\Fixtures\App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DummyController extends AbstractController
{
    public function testAction(): JsonResponse
    {
        return $this->json(['message' => 'Hello World!']);
    }
}
