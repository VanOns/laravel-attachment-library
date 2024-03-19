<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$rules = [
    '@PSR12' => true,
];

$finder = Finder::create()
                ->in([
                    __DIR__ . '/src',
                ])
                ->name('*.php')
                ->ignoreDotFiles(true)
                ->ignoreVCS(true);

return (new Config())
    ->setFinder($finder)
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setUsingCache(true);
