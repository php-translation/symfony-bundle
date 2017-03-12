<?php

namespace Translation\Bundle\Tests\Unit\DependencyInjection\CompilerPass;



use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Translation\Bundle\DependencyInjection\CompilerPass\EditInPlacePass;
use Translation\Bundle\DependencyInjection\CompilerPass\StoragePass;

class EditInPlacePassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EditInPlacePass());
    }
}
