<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller to render a basic "homepage".
 */
class DummyController extends AbstractController
{
    public function homepage()
    {
        $response = new Response();
        $response->setContent('hello');

        return $response;
    }

    public function exception()
    {
        throw new \RuntimeException('something went wrong');
    }
}
