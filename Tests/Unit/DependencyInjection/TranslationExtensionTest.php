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
    protected function getContainerExtensions(): array
    {
        $this->setParameter('kernel.default_locale', 'ar');
        $this->setParameter('kernel.project_dir', __DIR__);
        $this->setParameter('kernel.debug', true);

        return [
            new TranslationExtension(),
        ];
    }

    public function testLocales(): void
    {
        $locales = ['fr', 'sv'];
        $this->load(['locales' => $locales]);

        $this->assertContainerBuilderHasParameter('php_translation.locales', $locales);
        $this->assertContainerBuilderHasParameter('php_translation.default_locale', 'ar');
    }

    public function testDefaultLocales(): void
    {
        $this->load(['default_locale' => 'sv']);

        $this->assertContainerBuilderHasParameter('php_translation.default_locale', 'sv');
    }

    public function testWebUiEnabled(): void
    {
        $this->load(['webui' => ['enabled' => true]]);

        $this->assertContainerBuilderHasParameter('php_translation.webui.enabled', true);
    }

    public function testWebUiDisabled(): void
    {
        $this->load(['webui' => ['enabled' => false]]);

        $this->assertContainerBuilderHasParameter('php_translation.webui.enabled', false);
    }

    public function testSymfonyProfilerEnabled(): void
    {
        $this->load(['symfony_profiler' => ['enabled' => true]]);

        $this->assertContainerBuilderHasService('php_translation.data_collector', TranslationDataCollector::class);
    }

    public function testEditInPlaceEnabled(): void
    {
        $this->load(['edit_in_place' => ['enabled' => true]]);

        $this->assertContainerBuilderHasService('php_translation.edit_in_place.response_listener', EditInPlaceResponseListener::class);
    }

    public function testAutoAddEnabled(): void
    {
        $this->load(['auto_add_missing_translations' => ['enabled' => true]]);

        $this->assertContainerBuilderHasService('php_translator.auto_adder', AutoAddMissingTranslations::class);
    }

    public function testFallbackTranslationEnabled(): void
    {
        $this->load(['fallback_translation' => ['enabled' => true]]);

        $this->assertContainerBuilderHasService('php_translator.fallback_translator', FallbackTranslator::class);
    }
}
