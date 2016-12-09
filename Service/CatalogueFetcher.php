<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Service;

use Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Fetches catalogues from source files.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * @deprecated I think this could be removed.. Not sure.
 */
class CatalogueFetcher
{
    /**
     * @var TranslationLoader
     */
    private $loader;

    /**
     * @param TranslationLoader $loader
     */
    public function __construct(TranslationLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * load any existing messages from the translation files.
     *
     * @param array $locales
     * @param array $dirs
     *
     * @return MessageCatalogue[]
     */
    public function getCatalogues(array $locales, array $dirs)
    {
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
