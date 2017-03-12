<?php

namespace Translation\Bundle\Tests\Unit\DependencyInjection\CompilerPass;



use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Translation\Bundle\DependencyInjection\CompilerPass\ExternalTranslatorPass;
use Translation\Bundle\DependencyInjection\CompilerPass\StoragePass;

class ExternalTranslatorPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExternalTranslatorPass());
    }


    /**
     * @test
     */
    public function if_compiler_pass_collects_services_by_adding_method_calls_these_will_exist()
    {
        $collectingService = new Definition();
        $this->setDefinition('php_translation.translator_service.external_translator', $collectingService);

        $collectedService = new Definition();
        $collectedService->addTag('php_translation.external_translator');
        $this->setDefinition('collected_service', $collectedService);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'php_translation.translator_service.external_translator',
            'addTranslatorService',
            [new Reference('collected_service')]
        );
    }
}
