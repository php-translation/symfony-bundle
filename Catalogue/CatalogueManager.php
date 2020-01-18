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

use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\MetadataAwareInterface;
use Translation\Bundle\Model\CatalogueMessage;
use Translation\Bundle\Model\Metadata;

/**
 * A manager that handle loaded catalogues.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class CatalogueManager
{
    /**
     * @var MessageCatalogueInterface[]
     */
    private $catalogues = [];

    /**
     * @param MessageCatalogueInterface[] $catalogues
     */
    public function load(array $catalogues): void
    {
        $this->catalogues = [];
        foreach ($catalogues as $c) {
            $this->catalogues[$c->getLocale()] = $c;
        }
    }

    public function getDomains(): array
    {
        /** @var MessageCatalogueInterface $c */
        $c = \reset($this->catalogues);

        return $c->getDomains();
    }

    /**
     * @return CatalogueMessage[]
     */
    public function getMessages(string $locale, string $domain): array
    {
        $messages = [];
        if (!isset($this->catalogues[$locale])) {
            return $messages;
        }

        foreach ($this->catalogues[$locale]->all($domain) as $key => $text) {
            $messages[] = $this->createMessage($this->catalogues[$locale], $locale, $domain, $key, $text ?? '');
        }

        return $messages;
    }

    /**
     * @param array $config {
     *
     *      @var string $domain
     *      @var string $locale
     *      @var bool $isNew
     *      @var bool $isObsolete
     *      @var bool $isApproved
     * }
     *
     * @return CatalogueMessage[]
     */
    public function findMessages(array $config = []): array
    {
        $inputDomain = $config['domain'] ?? null;
        $isNew = $config['isNew'] ?? null;
        $isObsolete = $config['isObsolete'] ?? null;
        $isApproved = $config['isApproved'] ?? null;
        $isEmpty = $config['isEmpty'] ?? null;

        $messages = [];
        $catalogues = [];
        if (isset($config['locale'])) {
            $locale = $config['locale'];
            if (isset($this->catalogues[$locale])) {
                $catalogues = [$locale => $this->catalogues[$locale]];
            }
        } else {
            $catalogues = $this->catalogues;
        }

        foreach ($catalogues as $locale => $catalogue) {
            $domains = $catalogue->getDomains();
            if (null !== $inputDomain) {
                $domains = [$inputDomain];
            }
            foreach ($domains as $domain) {
                foreach ($catalogue->all($domain) as $key => $text) {
                    $messages[] = $this->createMessage($catalogue, $locale, $domain, $key, $text);
                }
            }
        }

        $messages = \array_filter($messages, static function (CatalogueMessage $m) use ($isNew, $isObsolete, $isApproved, $isEmpty) {
            if (null !== $isNew && $m->isNew() !== $isNew) {
                return false;
            }
            if (null !== $isObsolete && $m->isObsolete() !== $isObsolete) {
                return false;
            }
            if (null !== $isApproved && $m->isApproved() !== $isApproved) {
                return false;
            }
            if (null !== $isEmpty && empty($m->getMessage()) !== $isEmpty) {
                return false;
            }

            return true;
        });

        return $messages;
    }

    /**
     * @param string $domain
     * @param string $key
     */
    public function getTranslations($domain, $key): array
    {
        $translations = [];
        foreach ($this->catalogues as $locale => $catalogue) {
            if ($catalogue->has($key, $domain)) {
                $translations[$locale] = $catalogue->get($key, $domain);
            }
        }

        return $translations;
    }

    private function createMessage(MessageCatalogueInterface $catalogue, string $locale, string $domain, string $key, string $text): CatalogueMessage
    {
        $catalogueMessage = new CatalogueMessage($this, $locale, $domain, $key, $text);

        if ($catalogue instanceof MetadataAwareInterface) {
            $catalogueMessage->setMetadata(new Metadata($catalogue->getMetadata($key, $domain)));
        }

        return $catalogueMessage;
    }
}
