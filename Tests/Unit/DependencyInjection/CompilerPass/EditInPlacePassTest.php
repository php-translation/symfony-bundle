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
use Translation\Bundle\DependencyInjection\CompilerPass\EditInPlacePass;
use Translation\Bundle\Translator\EditInPlaceTranslator;

class EditInPlacePassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new EditInPlacePass());
    }

    public function testReplacement(): void
    {
        $def = new Definition();
        $this->setDefinition(EditInPlaceTranslator::class, $def);

        $twigExtension = new Definition();
        $twigExtension->addArgument('should_be_replaced');
        $this->setDefinition('twig.extension.trans', $twigExtension);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('twig.extension.trans', 0, new Reference(EditInPlaceTranslator::class));
    }
}
