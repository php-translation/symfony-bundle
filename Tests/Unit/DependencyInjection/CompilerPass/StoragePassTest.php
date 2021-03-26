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
use Symfony\Component\DependencyInjection\Reference;
use Translation\Bundle\DependencyInjection\CompilerPass\StoragePass;

class StoragePassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new StoragePass());
    }

    public function testIfCompilerPassCollectsServicesByAddingMethodCallsTheseWillExist(): void
    {
        $collectingService = new Definition();
        $this->setDefinition('php_translation.storage.foobar', $collectingService);

        $collectedService = new Definition();
        $collectedService->addTag('php_translation.storage', ['name' => 'foobar', 'type' => 'remote']);
        $this->setDefinition('collected_service', $collectedService);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'php_translation.storage.foobar',
            'addRemoteStorage',
            [new Reference('collected_service')]
        );
    }
}
