<?php

use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new App\Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};