<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller to render a basic "homepage".
 */
class DummyController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function homepage()
    {
        $response = new Response();
        $response->setContent('hello');

        return $response;
    }
}
