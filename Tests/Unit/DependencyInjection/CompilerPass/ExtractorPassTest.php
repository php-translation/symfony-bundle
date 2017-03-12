<?php

namespace Translation\Bundle\Tests\Unit\DependencyInjection\CompilerPass;



use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Translation\Bundle\DependencyInjection\CompilerPass\ExtractorPass;
use Translation\Bundle\DependencyInjection\CompilerPass\StoragePass;

class ExtractorPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExtractorPass());
    }
}
