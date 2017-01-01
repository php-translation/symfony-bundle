<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Catalogue;

use Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader;
use Symfony\Component\Translation\MessageCatalogue;
use Translation\Bundle\Service\ConfigurationManager;

/**
 * Fetches catalogues from source files. This will only work with local file storage
 * and the actions are read only.
 *
 * This should be considered as a ReadFromCache service.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class CatalogueFetcher
{
    /**
     * @var TranslationLoader
     */
    private $loader;

    /**
     * @var ConfigurationManager
     */
    private $configManager;

    /**
     *
     * @param TranslationLoader $loader
     * @param ConfigurationManager $configManager
     */
    public function __construct(
        TranslationLoader $loader,
        ConfigurationManager $configManager
    ) {
        $this->loader = $loader;
        $this->configManager = $configManager;
    }

    /**
     * load any existing messages from the translation files.
     *
     * @param string $configName
     * @param array  $locales
     *
     * @return MessageCatalogue[]
     */
    public function getCatalogues($configName, array $locales = [])
    {
        $config = $this->configManager->getConfiguration($configName);
        $dirs = $config->getPathsToTranslationFiles();
        if (empty($locales)) {
            $locales = $config->getLocales();
        }
        $catalogues = [];
        foreach ($locales as $locale) {
            $currentCatalogue = new MessageCatalogue($locale);
            foreach ($dirs as $path) {
                if (is_dir($path)) {
                    $this->loader->loadMessages($path, $currentCatalogue);
                }
            }
            $catalogues[] = $currentCatalogue;
        }

        return $catalogues;
    }
}
