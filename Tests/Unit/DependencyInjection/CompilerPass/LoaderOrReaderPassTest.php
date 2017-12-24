<?php

namespace Translation\Bundle\Tests\Unit\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Translation\Bundle\DependencyInjection\CompilerPass\LoaderOrReaderPass;


class LoaderOrReaderPassTest extends AbstractCompilerPassTestCase
{

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new LoaderOrReaderPass());
    }

    public function testLoaderOrReader()
    {
        $def = new Definition();
        $this->setDefinition('translation.reader', $def);

        $this->compile();

        $this->assertContainerBuilderHasAlias('translation.loader.or.reader');
    }
}
