<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude(__DIR__.'/vendor')
    ->name('*.php')
    ->in(__DIR__)
;

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'native_function_invocation' => true,
        'ordered_imports' => true,
        'declare_strict_types' => false,
        'single_import_per_statement' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
