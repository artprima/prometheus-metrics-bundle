<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DemoController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return new Response('<h1>Symfony Prometheus Demo</h1><p>Visit <a href="/api/users">/api/users</a> or <a href="/api/error">/api/error</a></p>');
    }

    #[Route('/api/users', name: 'api_users')]
    public function users(): JsonResponse
    {
        // Simulate random response time
        usleep(random_int(10000, 500000));
        
        return new JsonResponse([
            'users' => [
                ['id' => 1, 'name' => 'John Doe'],
                ['id' => 2, 'name' => 'Jane Smith'],
            ]
        ]);
    }

    #[Route('/api/error', name: 'api_error')]
    public function error(): Response
    {
        // Simulate random errors
        if (random_int(1, 3) === 1) {
            throw new \Exception('Random error occurred');
        }
        
        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/api/slow', name: 'api_slow')]
    public function slow(): JsonResponse
    {
        // Simulate slow endpoint
        sleep(2);
        return new JsonResponse(['status' => 'slow response']);
    }

    #[Route('/health', name: 'health')]
    public function health(): JsonResponse
    {
        return new JsonResponse(['status' => 'healthy', 'timestamp' => time()]);
    }
}