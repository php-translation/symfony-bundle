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

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Translation\Bundle\Catalogue\MetadataWriter;
use Translation\Bundle\Model\ImportResult;
use Translation\Extractor\Extractor;
use Translation\Extractor\Model\SourceCollection;
use Translation\Extractor\Model\SourceLocation;
use Translation\Bundle\Catalogue\Operation\MetadataAwareMerge;

/**
 * Use extractors to import translations to message catalogues.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class Importer
{
    /**
     * @var Extractor
     */
    private $extractor;

    /**
     * @var array
     */
    private $config;

    /**
     * @var MetadataWriter
     */
    private $metadataWriter;

    /**
     * @param Extractor      $extractor
     * @param MetadataWriter $metadataWriter
     */
    public function __construct(Extractor $extractor, MetadataWriter $metadataWriter)
    {
        $this->extractor = $extractor;
        $this->metadataWriter = $metadataWriter;
    }

    /**
     * @param Finder             $finder
     * @param MessageCatalogue[] $catalogues
     * @param array              $config     {
     *
     *     @var array $blacklist_domains Blacklist the domains we should exclude. Cannot be used with whitelist.
     *     @var array $whitelist_domains Whitlelist the domains we should include. Cannot be used with blacklist.
     *     @var string project_root The project root will be removed from the source location.
     * }
     *
     * @return ImportResult
     */
    public function extractToCatalogues(Finder $finder, array $catalogues, array $config = [])
    {
        $this->processConfig($config);
        $sourceCollection = $this->extractor->extract($finder);
        $results = [];
        foreach ($catalogues as $catalogue) {
            $target = new MessageCatalogue($catalogue->getLocale());
            $this->convertSourceLocationsToMessages($target, $sourceCollection);

            $merge = new MetadataAwareMerge($catalogue, $target);
            $result = $merge->getResult();
            $domains = $merge->getDomains();

            // Mark new messages as new/obsolete
            foreach ($domains as $domain) {
                foreach ($merge->getNewMessages($domain) as $key => $translation) {
                    $this->metadataWriter->write($result, $key, $domain, 'notes', ['content' => 'status:new']);
                }
                foreach ($merge->getObsoleteMessages($domain) as $key => $translation) {
                    $this->metadataWriter->write($result, $key, $domain, 'notes', ['content' => 'status:obsolete']);
                }
            }
            $results[] = $result;
        }

        return new ImportResult($results, $sourceCollection->getErrors());
    }

    /**
     * See docs for extractToCatalogues.
     *
     * @return MessageCatalogue
     *
     * @deprecated use extractToCatalogues instead.
     */
    public function extractToCatalogue(Finder $finder, MessageCatalogue $catalogue, array $config = [])
    {
        $result = $this->extractToCatalogues($finder, [$catalogue], $config);
        $messageCatalogue = $result->getMessageCatalogues();

        return reset($messageCatalogue);
    }

    /**
     * @param MessageCatalogue $catalogue
     * @param SourceCollection $collection
     */
    private function convertSourceLocationsToMessages(MessageCatalogue $catalogue, SourceCollection $collection)
    {
        /** @var SourceLocation $sourceLocation */
        foreach ($collection as $sourceLocation) {
            $context = $sourceLocation->getContext();
            $domain = isset($context['domain']) ? $context['domain'] : 'messages';
            // Check with white/black list
            if (!$this->isValidDomain($domain)) {
                continue;
            }

            $catalogue->set($sourceLocation->getMessage(), null, $domain);
            $trimLength = 1 + strlen($this->config['project_root']);

            $this->metadataWriter->write(
                $catalogue,
                $sourceLocation->getMessage(),
                $domain,
                'notes',
                ['from' => sprintf('%s:%s', substr($sourceLocation->getPath(), $trimLength), $sourceLocation->getLine()), 'content' => 'file-source']
            );
        }
    }

    /**
     * @param string $domain
     *
     * @return bool
     */
    private function isValidDomain($domain)
    {
        if (!empty($this->config['blacklist_domains']) && in_array($domain, $this->config['blacklist_domains'])) {
            return false;
        }
        if (!empty($this->config['whitelist_domains']) && !in_array($domain, $this->config['whitelist_domains'])) {
            return false;
        }

        return true;
    }

    /**
     * Make sure the configuration is valid.
     *
     * @param array $config
     */
    private function processConfig($config)
    {
        $default = [
            'project_root' => '',
            'blacklist_domains' => [],
            'whitelist_domains' => [],
        ];

        $config = array_merge($default, $config);

        if (!empty($config['blacklist_domains']) && !empty($config['whitelist_domains'])) {
            throw new \InvalidArgumentException('Cannot use "blacklist_domains" and "whitelist_domains" at the same time');
        }

        if (!empty($config['blacklist_domains']) && !is_array($config['blacklist_domains'])) {
            throw new \InvalidArgumentException('Config parameter "blacklist_domains" must be an array');
        }

        if (!empty($config['whitelist_domains']) && !is_array($config['whitelist_domains'])) {
            throw new \InvalidArgumentException('Config parameter "whitelist_domains" must be an array');
        }

        $this->config = $config;
    }
}
