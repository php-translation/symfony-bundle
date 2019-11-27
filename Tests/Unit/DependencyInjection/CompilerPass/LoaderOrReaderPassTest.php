<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Tests\Unit\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Translation\Bundle\DependencyInjection\CompilerPass\LoaderOrReaderPass;

class LoaderOrReaderPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new LoaderOrReaderPass());
    }

    public function testLoaderOrReader(): void
    {
        $def = new Definition();
        $this->setDefinition('translation.reader', $def);

        $this->compile();

        $this->assertContainerBuilderHasAlias('translation.loader_or_reader');
    }
}
