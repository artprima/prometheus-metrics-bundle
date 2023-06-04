<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('config')
;

$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
    ])
    ->setFinder($finder)
;
