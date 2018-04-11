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

use Nyholm\NSA;
use Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader as SymfonyTranslationLoader;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Reader\TranslationReaderInterface;
use Translation\Bundle\Model\Configuration;
use Translation\SymfonyStorage\LegacyTranslationReader;
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
     * @var TranslationReaderInterface
     */
    private $reader;

    /**
     * @param SymfonyTranslationLoader|TranslationLoader|TranslationReaderInterface $reader
     */
    public function __construct($reader)
    {
        if (!$reader instanceof TranslationReaderInterface) {
            $reader = new LegacyTranslationReader($reader);
        }

        $this->reader = $reader;
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
                    $this->reader->read($path, $currentCatalogue);
                }
            }

            foreach ($currentCatalogue->getDomains() as $domain) {
                if (!$this->isValidDomain($config, $domain)) {
                    $messages = $currentCatalogue->all();
                    unset($messages[$domain]);
                    NSA::setProperty($currentCatalogue, 'messages', $messages);
                }
            }

            $catalogues[] = $currentCatalogue;
        }

        return $catalogues;
    }

    /**
     * @param string $domain
     *
     * @return bool
     */
    private function isValidDomain(Configuration $config, $domain)
    {
        $blacklist = $config->getBlacklistDomains();
        $whitelist = $config->getWhitelistDomains();

        if (!empty($blacklist) && in_array($domain, $blacklist)) {
            return false;
        }

        if (!empty($whitelist) && !in_array($domain, $whitelist)) {
            return false;
        }

        return true;
    }
}
