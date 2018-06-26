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
use Translation\Bundle\Model\ImportResult;
use Translation\Bundle\Model\Metadata;
use Translation\Bundle\Twig\Visitor\DefaultApplyingNodeVisitor;
use Translation\Bundle\Twig\Visitor\RemovingNodeVisitor;
use Translation\Extractor\Extractor;
use Translation\Extractor\Model\SourceCollection;
use Translation\Extractor\Model\SourceLocation;
use Translation\Bundle\Catalogue\Operation\ReplaceOperation;

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
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @param Extractor         $extractor
     * @param \Twig_Environment $twig
     * @param string            $defaultLocale
     */
    public function __construct(Extractor $extractor, \Twig_Environment $twig, $defaultLocale)
    {
        $this->extractor = $extractor;
        $this->twig = $twig;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param Finder             $finder
     * @param MessageCatalogue[] $catalogues
     * @param array              $config     {
     *
     *     @var array $blacklist_domains Blacklist the domains we should exclude. Cannot be used with whitelist.
     *     @var array $whitelist_domains Whitelist the domains we should include. Cannot be used with blacklist.
     *     @var string $project_root The project root will be removed from the source location.
     * }
     *
     * @return ImportResult
     */
    public function extractToCatalogues(Finder $finder, array $catalogues, array $config = [])
    {
        $this->processConfig($config);
        $this->disableTwigVisitors();
        $sourceCollection = $this->extractor->extract($finder);
        $results = [];
        foreach ($catalogues as $catalogue) {
            $target = new MessageCatalogue($catalogue->getLocale());
            $this->convertSourceLocationsToMessages($target, $sourceCollection);

            // Remove all SourceLocation and State form catalogue.
            foreach ($catalogue->getDomains() as $domain) {
                foreach ($catalogue->all($domain) as $key => $translation) {
                    $meta = $this->getMetadata($catalogue, $key, $domain);
                    $meta->removeAllInCategory('file-source');
                    $meta->removeAllInCategory('state');
                    $this->setMetadata($catalogue, $key, $domain, $meta);
                }
            }

            $merge = new ReplaceOperation($target, $catalogue);
            $result = $merge->getResult();
            $domains = $merge->getDomains();

            // Mark new messages as new/obsolete
            foreach ($domains as $domain) {
                foreach ($merge->getNewMessages($domain) as $key => $translation) {
                    $meta = $this->getMetadata($result, $key, $domain);
                    $meta->setState('new');
                    $this->setMetadata($result, $key, $domain, $meta);

                    // Add custom translations that we found in the source
                    if (null === $translation) {
                        if (null !== $newTranslation = $meta->getTranslation()) {
                            $result->set($key, $newTranslation, $domain);
                            // We do not want "translation" key stored anywhere.
                            $meta->removeAllInCategory('translation');
                        } elseif (null !== $newTranslation = $meta->getDesc() && $catalogue->getLocale() === $this->defaultLocale) {
                            $result->set($key, $newTranslation, $domain);
                        }
                    }
                }
                foreach ($merge->getObsoleteMessages($domain) as $key => $translation) {
                    $meta = $this->getMetadata($result, $key, $domain);
                    $meta->setState('obsolete');
                    $this->setMetadata($result, $key, $domain, $meta);
                }
            }
            $results[] = $result;
        }

        return new ImportResult($results, $sourceCollection->getErrors());
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

            $key = $sourceLocation->getMessage();
            $catalogue->set($key, null, $domain);
            $trimLength = 1 + strlen($this->config['project_root']);

            $meta = $this->getMetadata($catalogue, $key, $domain);
            $meta->addCategory('file-source', sprintf('%s:%s', substr($sourceLocation->getPath(), $trimLength), $sourceLocation->getLine()));
            if (isset($sourceLocation->getContext()['desc'])) {
                $meta->addCategory('desc', $sourceLocation->getContext()['desc']);
            }
            if (isset($sourceLocation->getContext()['translation'])) {
                $meta->addCategory('translation', $sourceLocation->getContext()['translation']);
            }
            $this->setMetadata($catalogue, $key, $domain, $meta);
        }
    }

    /**
     * @param MessageCatalogue $catalogue
     * @param $key
     * @param $domain
     *
     * @return Metadata
     */
    private function getMetadata(MessageCatalogue $catalogue, $key, $domain)
    {
        return new Metadata($catalogue->getMetadata($key, $domain));
    }

    /**
     * @param MessageCatalogue $catalogue
     * @param $key
     * @param $domain
     * @param Metadata $metadata
     */
    private function setMetadata(MessageCatalogue $catalogue, $key, $domain, Metadata $metadata)
    {
        $catalogue->setMetadata($key, $metadata->toArray(), $domain);
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

    private function disableTwigVisitors()
    {
        foreach ($this->twig->getNodeVisitors() as $visitor) {
            if ($visitor instanceof DefaultApplyingNodeVisitor) {
                $visitor->setEnabled(false);
            }
            if ($visitor instanceof RemovingNodeVisitor) {
                $visitor->setEnabled(false);
            }
        }
    }
}
