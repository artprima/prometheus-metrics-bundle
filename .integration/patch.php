<?php

declare(strict_types=1);

function patch_composer_json(string $branchname): void
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
        'type' => 'vcs',
        'url' => '../',
    ]];
    $json['require']['artprima/prometheus-metrics-bundle'] = 'dev-'.$branchname;
    $json['require-dev'] = (object)$json['require-dev'];
    $data = json_encode($json, JSON_PRETTY_PRINT);
    if ($data === false) {
        exit(-1);
    }

    $result = file_put_contents('symfony/composer.json', $data);
    if ($result === false) {
        exit(-1);
    }
}

// https://stackoverflow.com/questions/7447472/how-could-i-display-the-current-git-branch-name-at-the-top-of-the-page-of-my-de
function get_branch_name(): string
{
    $stringfromfile = file('.git/HEAD', FILE_USE_INCLUDE_PATH);
    $firstLine = $stringfromfile[0]; //get the string from the array
    $explodedstring = explode("/", $firstLine, 3); //seperate out by the "/" in the string
    $branchname = $explodedstring[2]; //get the one that is always the branch name

    return $branchname;
}

patch_composer_json(get_branch_name());
