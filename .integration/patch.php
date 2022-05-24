<?php

declare(strict_types=1);

function patch_composer_json(): void
{
    if (!file_exists('symfony/composer.json')) {
        echo 'symfony/composer.json not found!'.PHP_EOL;
        exit(-1);
    }

    $contents = file_get_contents('symfony/composer.json');
    if ($contents === false) {
        exit(-1);
    }

    $json = json_decode($contents, true);
    $json['repositories'] = [[
        'type' => 'path',
        'url' => realpath(__DIR__.'/../'),
        'options' => [
            'versions' => [
                'artprima/prometheus-metrics-bundle' => '1-dev',
            ],
            'symlink' => false,
        ]
    ]];
    $json['require-dev'] = (object)$json['require-dev'];
    $json['minimum-stability'] = 'dev';
    $json['prefer-stable'] = true;
    $json['extra']['symfony']['allow-contrib'] = false;

    $data = json_encode($json, JSON_PRETTY_PRINT);
    if ($data === false) {
        exit(-1);
    }

    $result = file_put_contents('symfony/composer.json', $data);
    if ($result === false) {
        exit(-1);
    }
}

patch_composer_json();
