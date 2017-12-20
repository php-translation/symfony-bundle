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

    /**
     * @param CatalogueManager $catalogueManager
     * @param string           $locale
     * @param string           $domain
     * @param string           $key
     * @param string           $message
     */
    public function __construct(CatalogueManager $catalogueManager, $locale, $domain, $key, $message)
    {
        $this->catalogueManager = $catalogueManager;
        $this->locale = $locale;
        $this->domain = $domain;
        $this->key = $key;
        $this->message = $message;
    }

    /**
     * @param null|Metadata $metadata
     */
    public function setMetadata(Metadata $metadata)
    {
        $this->metadata = $metadata;
    }

    public function __toString()
    {
        return $this->getMessage();
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function getOtherTranslations()
    {
        $translations = $this->catalogueManager->getTranslations($this->domain, $this->getKey());

        unset($translations[$this->locale]);

        return $translations;
    }

    public function getSourceLocations()
    {
        if (null === $this->metadata) {
            return [];
        }

        return $this->metadata->getSourceLocations();
    }

    public function isNew()
    {
        if (null === $this->metadata) {
            return false;
        }

        return 'new' === $this->metadata->getState();
    }

    public function isObsolete()
    {
        if (null === $this->metadata) {
            return false;
        }

        return 'obsolete' === $this->metadata->getState();
    }

    public function isApproved()
    {
        if (null === $this->metadata) {
            return false;
        }

        return $this->metadata->isApproved();
    }
}
