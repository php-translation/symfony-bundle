<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Translation\Bundle\Catalogue\CatalogueCounter;
use Translation\Bundle\Catalogue\CatalogueFetcher;
use Translation\Bundle\Catalogue\CatalogueManager;
use Translation\Bundle\Catalogue\CatalogueWriter;
use Translation\Bundle\Legacy\LegacyHelper;
use Translation\Bundle\Service\CacheClearer;
use Translation\Bundle\Service\ConfigurationManager;
use Translation\Bundle\Service\Importer;
use Translation\Bundle\Service\StorageManager;
use Translation\Bundle\Twig\TranslationExtension;
use Translation\Extractor\Extractor;

return function (ContainerConfigurator $configurator) {
    LegacyHelper::registerDeprecatedServices($configurator->services(), [
        ['php_translation.catalogue_fetcher', CatalogueFetcher::class, true],
        ['php_translation.catalogue_writer', CatalogueWriter::class, true],
        ['php_translation.catalogue_manager', CatalogueManager::class, true],
        ['php_translation.extractor', Extractor::class],
        ['php_translation.storage_manager', StorageManager::class, true],
        ['php_translation.configuration_manager', ConfigurationManager::class, true],
        ['php_translation.importer', Importer::class, true],
        ['php_translation.cache_clearer', CacheClearer::class, true],
        ['php_translation.catalogue_counter', CatalogueCounter::class, true],
        ['php_translation.twig_extension', TranslationExtension::class],
    ]);
};
