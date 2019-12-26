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
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Reader\TranslationReaderInterface;
use Translation\Bundle\Model\Configuration;

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
    private $reader;

    public function __construct(TranslationReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * load any existing messages from the translation files.
     */
    public function getCatalogues(Configuration $config, array $locales = []): array
    {
        $dirs = $config->getPathsToTranslationFiles();
        if (empty($locales)) {
            $locales = $config->getLocales();
        }
        $catalogues = [];
        foreach ($locales as $locale) {
            $currentCatalogue = new MessageCatalogue($locale);
            foreach ($dirs as $path) {
                if (\is_dir($path)) {
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

    private function isValidDomain(Configuration $config, string $domain): bool
    {
        $blacklist = $config->getBlacklistDomains();
        $whitelist = $config->getWhitelistDomains();

        if (!empty($blacklist) && \in_array($domain, $blacklist, true)) {
            return false;
        }

        if (!empty($whitelist) && !\in_array($domain, $whitelist, true)) {
            return false;
        }

        return true;
    }
}
