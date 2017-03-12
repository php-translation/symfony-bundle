<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Tests\Unit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\Translation\DataCollector\TranslationDataCollector;
use Translation\Bundle\DependencyInjection\TranslationExtension;
use Translation\Bundle\EventListener\AutoAddMissingTranslations;
use Translation\Bundle\EventListener\EditInPlaceResponseListener;
use Translation\Bundle\Translator\FallbackTranslator;

class TranslationExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        $this->setParameter('kernel.default_locale', 'ar');
        $this->setParameter('kernel.root_dir', __DIR__);

        return [
            new TranslationExtension(),
        ];
    }

    public function testLocales()
    {
        $locales = ['fr', 'sv'];
        $this->load(['locales' => $locales]);

        $this->assertContainerBuilderHasParameter('php_translation.locales', $locales);
        $this->assertContainerBuilderHasParameter('php_translation.default_locale', 'ar');
    }

    public function testDefaultLocales()
    {
        $this->load(['default_locale' => 'sv']);

        $this->assertContainerBuilderHasParameter('php_translation.default_locale', 'sv');
    }

    public function testWebUiEnabled()
    {
        $this->load(['webui' => ['enabled' => true]]);

        $this->assertContainerBuilderHasParameter('php_translation.webui.enabled', true);
    }

    public function testWebUiDisabled()
    {
        $this->load(['webui' => ['enabled' => false]]);

        $this->assertContainerBuilderHasParameter('php_translation.webui.enabled', false);
    }

    public function testSymfonyProfilerEnabled()
    {
        $this->load(['symfony_profiler' => ['enabled' => true]]);

        $this->assertContainerBuilderHasService('php_translation.data_collector', TranslationDataCollector::class);
    }

    public function testEditInPlaceEnabled()
    {
        $this->load(['edit_in_place' => ['enabled' => true]]);

        $this->assertContainerBuilderHasService('php_translation.edit_in_place.response_listener', EditInPlaceResponseListener::class);
    }

    public function testAutoAddEnabled()
    {
        $this->load(['auto_add_missing_translations' => ['enabled' => true]]);

        $this->assertContainerBuilderHasService('php_translator.auto_adder', AutoAddMissingTranslations::class);
    }

    public function testFallbackTranslationEnabled()
    {
        $this->load(['fallback_translation' => ['enabled' => true]]);

        $this->assertContainerBuilderHasService('php_translator.fallback_translator', FallbackTranslator::class);
    }
}
