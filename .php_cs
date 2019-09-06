<?php

$config = PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => array('syntax' => 'short'),
        'native_function_invocation' => true,
        'ordered_imports' => true,
        'declare_strict_types' => false,
        'single_import_per_statement' => false,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->exclude(__DIR__.'/vendor')
            ->name('*.php')
    )
;

return $config;
