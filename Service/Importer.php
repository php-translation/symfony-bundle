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

use Nyholm\NSA;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Translation\Bundle\Catalogue\Operation\ReplaceOperation;
use Translation\Bundle\Model\ImportResult;
use Translation\Bundle\Model\Metadata;
use Translation\Bundle\Twig\Visitor\DefaultApplyingNodeVisitor;
use Translation\Bundle\Twig\Visitor\RemovingNodeVisitor;
use Translation\Extractor\Extractor;
use Translation\Extractor\Model\SourceCollection;
use Translation\Extractor\Model\SourceLocation;
use Twig\Environment;

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
     * @var Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $defaultLocale;

    public function __construct(Extractor $extractor, Environment $twig, string $defaultLocale)
    {
        $this->extractor = $extractor;
        $this->twig = $twig;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param MessageCatalogue[] $catalogues
     * @param array              $config     {
     *
     *     @var array $blacklist_domains Blacklist the domains we should exclude. Cannot be used with whitelist.
     *     @var array $whitelist_domains Whitelist the domains we should include. Cannot be used with blacklist.
     *     @var string $project_root The project root will be removed from the source location.
     * }
     */
    public function extractToCatalogues(Finder $finder, array $catalogues, array $config = []): ImportResult
    {
        $this->processConfig($config);
        $this->disableTwigVisitors();
        $sourceCollection = $this->extractor->extract($finder);
        $results = [];
        foreach ($catalogues as $catalogue) {
            $target = new MessageCatalogue($catalogue->getLocale());
            $this->convertSourceLocationsToMessages($target, $sourceCollection, $catalogue);

            // Remove all SourceLocation and State form catalogue.
            foreach (NSA::getProperty($catalogue, 'messages') as $domain => $translations) {
                foreach ($translations as $key => $translation) {
                    $meta = $this->getMetadata($catalogue, $key, $domain);
                    $meta->removeAllInCategory('file-source');
                    $meta->removeAllInCategory('state');
                    $this->setMetadata($catalogue, $key, $domain, $meta);
                }
            }

            $merge = new ReplaceOperation($target, $catalogue);
            $result = $merge->getResult();
            $domains = $merge->getDomains();

            $resultMessages = NSA::getProperty($result, 'messages');

            // Mark new messages as new/obsolete
            foreach ($domains as $domain) {
                $intlDomain = $domain . '+intl-icu' /* MessageCatalogueInterface::INTL_DOMAIN_SUFFIX */;

                foreach ($merge->getNewMessages($domain) as $key => $translation) {
                    $messageDomain = array_key_exists($key, $resultMessages[$intlDomain] ?? []) ? $intlDomain : $domain;

                    $meta = $this->getMetadata($result, $key, $messageDomain);
                    $meta->setState('new');
                    $this->setMetadata($result, $key, $messageDomain, $meta);

                    // Add custom translations that we found in the source
                    if (null === $translation) {
                        if (null !== $newTranslation = $meta->getTranslation()) {
                            $result->set($key, $newTranslation, $messageDomain);
                            // We do not want "translation" key stored anywhere.
                            $meta->removeAllInCategory('translation');
                        } elseif (null !== ($newTranslation = $meta->getDesc()) && $catalogue->getLocale() === $this->defaultLocale) {
                            $result->set($key, $newTranslation, $messageDomain);
                        }
                    }
                }

                foreach ($merge->getObsoleteMessages($domain) as $key => $translation) {
                    $messageDomain = array_key_exists($key, $resultMessages[$intlDomain] ?? []) ? $intlDomain : $domain;

                    $meta = $this->getMetadata($result, $key, $messageDomain);
                    $meta->setState('obsolete');
                    $this->setMetadata($result, $key, $messageDomain, $meta);
                }
            }
            $results[] = $result;
        }

        return new ImportResult($results, $sourceCollection->getErrors());
    }

    private function convertSourceLocationsToMessages(
        MessageCatalogue $catalogue,
        SourceCollection $collection,
        MessageCatalogue $currentCatalogue
    ): void {
        $currentMessages = NSA::getProperty($currentCatalogue, 'messages');

        /** @var SourceLocation $sourceLocation */
        foreach ($collection as $sourceLocation) {
            $context = $sourceLocation->getContext();
            $domain = $context['domain'] ?? 'messages';

            // Check with white/black list
            if (!$this->isValidDomain($domain)) {
                continue;
            }

            $intlDomain = $domain . '+intl-icu' /* MessageCatalogueInterface::INTL_DOMAIN_SUFFIX */;

            $key = $sourceLocation->getMessage();

            if (array_key_exists($key, $currentMessages[$intlDomain] ?? [])) {
                $messageDomain = $intlDomain;
            } elseif (array_key_exists($key, $currentMessages[$domain] ?? [])) {
                $messageDomain = $domain;
            } else {
                // New translation
                $messageDomain = 'icu' === $this->config['new_message_format'] ? $intlDomain : $domain;
            }

            $catalogue->add([$key => null], $messageDomain);
            $trimLength = 1 + \strlen($this->config['project_root']);

            $meta = $this->getMetadata($catalogue, $key, $messageDomain);
            $meta->addCategory('file-source', \sprintf('%s:%s', \substr($sourceLocation->getPath(), $trimLength), $sourceLocation->getLine()));
            if (isset($sourceLocation->getContext()['desc'])) {
                $meta->addCategory('desc', $sourceLocation->getContext()['desc']);
            }
            if (isset($sourceLocation->getContext()['translation'])) {
                $meta->addCategory('translation', $sourceLocation->getContext()['translation']);
            }
            $this->setMetadata($catalogue, $key, $messageDomain, $meta);
        }
    }

    private function getMetadata(MessageCatalogue $catalogue, string $key, string $domain): Metadata
    {
        return new Metadata($catalogue->getMetadata($key, $domain));
    }

    private function setMetadata(MessageCatalogue $catalogue, string $key, string $domain, Metadata $metadata): void
    {
        $catalogue->setMetadata($key, $metadata->toArray(), $domain);
    }

    private function isValidDomain(string $domain): bool
    {
        if (!empty($this->config['blacklist_domains']) && \in_array($domain, $this->config['blacklist_domains'], true)) {
            return false;
        }
        if (!empty($this->config['whitelist_domains']) && !\in_array($domain, $this->config['whitelist_domains'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Make sure the configuration is valid.
     */
    private function processConfig(array $config): void
    {
        $default = [
            'project_root' => '',
            'blacklist_domains' => [],
            'whitelist_domains' => [],
            'new_message_format' => 'icu',
        ];

        $config = \array_merge($default, $config);

        if (!empty($config['blacklist_domains']) && !empty($config['whitelist_domains'])) {
            throw new \InvalidArgumentException('Cannot use "blacklist_domains" and "whitelist_domains" at the same time');
        }

        if (!empty($config['blacklist_domains']) && !\is_array($config['blacklist_domains'])) {
            throw new \InvalidArgumentException('Config parameter "blacklist_domains" must be an array');
        }

        if (!empty($config['whitelist_domains']) && !\is_array($config['whitelist_domains'])) {
            throw new \InvalidArgumentException('Config parameter "whitelist_domains" must be an array');
        }

        $this->config = $config;
    }

    private function disableTwigVisitors(): void
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
