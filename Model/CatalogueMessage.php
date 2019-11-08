<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Model;

use Translation\Bundle\Catalogue\CatalogueManager;

/**
 * A message representation for CatalogueManager.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class CatalogueMessage
{
    /**
     * @var CatalogueManager
     */
    private $catalogueManager;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var Metadata|null
     */
    private $metadata;

    public function __construct(CatalogueManager $catalogueManager, string $locale, string $domain, string $key, string $message)
    {
        $this->catalogueManager = $catalogueManager;
        $this->locale = $locale;
        $this->domain = $domain;
        $this->key = $key;
        $this->message = $message;
    }

    public function setMetadata(Metadata $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function __toString(): string
    {
        return $this->getMessage();
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getOtherTranslations(): array
    {
        $translations = $this->catalogueManager->getTranslations($this->domain, $this->getKey());

        unset($translations[$this->locale]);

        return $translations;
    }

    public function getSourceLocations(): array
    {
        if (null === $this->metadata) {
            return [];
        }

        return $this->metadata->getSourceLocations();
    }

    public function isNew(): bool
    {
        if (null === $this->metadata) {
            return false;
        }

        return 'new' === $this->metadata->getState();
    }

    public function isObsolete(): bool
    {
        if (null === $this->metadata) {
            return false;
        }

        return 'obsolete' === $this->metadata->getState();
    }

    public function isApproved(): bool
    {
        if (null === $this->metadata) {
            return false;
        }

        return $this->metadata->isApproved();
    }
}
