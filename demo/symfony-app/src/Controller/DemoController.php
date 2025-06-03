<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class DemoController extends AbstractController implements ServiceSubscriberInterface
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return new Response('
            <h1>Symfony Prometheus Demo</h1>
            <h2>Available Endpoints:</h2>
            <ul>
                <li><a href="/api/users">/api/users</a> - User data endpoint</li>
                <li><a href="/api/error">/api/error</a> - Random error endpoint</li>
                <li><a href="/api/database-error">/api/database-error</a> - Database error simulation</li>
                <li><a href="/api/validation-error">/api/validation-error</a> - Validation error simulation</li>
                <li><a href="/api/slow">/api/slow</a> - Slow response endpoint</li>
                <li><a href="/health">/health</a> - Health check endpoint</li>
                <li><a href="/metrics/prometheus">/metrics/prometheus</a> - Prometheus metrics</li>
            </ul>
        ');
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
        // Simulate random errors with different exception types
        $errorType = random_int(1, 5);
        
        switch ($errorType) {
            case 1:
                throw new \Exception('Generic exception occurred');
            case 2:
                throw new \RuntimeException('Runtime error in processing');
            case 3:
                throw new \InvalidArgumentException('Invalid parameters provided');
            case 4:
                throw new \LogicException('Logic error in application flow');
            default:
                // 20% chance of success to make errors more realistic
                return new JsonResponse(['status' => 'ok']);
        }
    }

    #[Route('/api/database-error', name: 'api_database_error')]
    public function databaseError(): Response
    {
        // Simulate database-related errors
        if (random_int(1, 2) === 1) {
            throw new \PDOException('Database connection failed');
        }
        
        return new JsonResponse(['status' => 'database ok']);
    }

    #[Route('/api/validation-error', name: 'api_validation_error')]
    public function validationError(): Response
    {
        // Simulate validation errors
        $errors = [
            new \InvalidArgumentException('Email format is invalid'),
            new \UnexpectedValueException('Unexpected value in input'),
            new \OutOfBoundsException('Value is out of acceptable range')
        ];
        
        if (random_int(1, 3) === 1) {
            throw $errors[array_rand($errors)];
        }
        
        return new JsonResponse(['status' => 'validation passed']);
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