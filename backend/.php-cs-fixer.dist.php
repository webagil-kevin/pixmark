<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
    ->in([__DIR__.'/src', __DIR__.'/tests'])    // Répertoires à analyser
    ->exclude(['var', 'vendor'])                // Répertoires à exclure
    ->name('*.php')                     // Analyser les fichiers PHP uniquement
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'protected_to_private' => false,
        'array_syntax' => ['syntax' => 'short'],
        'single_quote' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => true,
        ],
        'single_line_throw' => false,
        'fully_qualified_strict_types' => true,
        'modernize_strpos' => true,
    ])
    ->setRiskyAllowed(true)
    ->setLineEnding("\n")
    ->setCacheFile(__DIR__.'/var/.php-cs-fixer.cache')
    ->setFinder($finder);
