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

use Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader as SymfonyTranslationLoader;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Reader\TranslationReader;
use Translation\Bundle\Model\Configuration;
use Translation\SymfonyStorage\LegacyTranslationLoader;
use Translation\SymfonyStorage\TranslationLoader;

/**
 * Fetches catalogues from source files. This will only work with local file storage
 * and the actions are read only.
 *
 * This should be considered as a "ReadFromCache" service.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class CatalogueFetcher
{
    /**
     * @var TranslationLoader|SymfonyTranslationLoader
     */
    private $loader;

    /**
     * @param SymfonyTranslationLoader|TranslationLoader|TranslationReader $loader
     */
    public function __construct($loader)
    {
        // Create a legacy loader which is a wrapper for TranslationReader
        if ($loader instanceof TranslationReader) {
            $loader = new LegacyTranslationLoader($loader);
        }
        if (!$loader instanceof SymfonyTranslationLoader && !$loader instanceof TranslationLoader) {
            throw new \LogicException('First parameter of CatalogueFetcher must be a Symfony translation loader or implement Translation\SymfonyStorage\TranslationLoader');
        }

        $this->loader = $loader;
    }

    /**
     * load any existing messages from the translation files.
     *
     * @param Configuration $config
     * @param array         $locales
     *
     * @return MessageCatalogue[]
     */
    public function getCatalogues(Configuration $config, array $locales = [])
    {
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
