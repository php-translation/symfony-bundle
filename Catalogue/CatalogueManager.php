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

use Symfony\Component\Translation\MessageCatalogue;
use Translation\Bundle\Model\Message;

/**
 * A manager that handle loaded catalogues.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class CatalogueManager
{
    /**
     * @var MessageCatalogue[]
     */
    private $catalogues;

    /**
     * @var string
     */
    private $projectRoot;

    /**
     * @param string $projectRoot
     */
    public function __construct($projectRoot)
    {
        $this->projectRoot = $projectRoot;
    }

    /**
     * @param MessageCatalogue[] $catalogues
     */
    public function load(array $catalogues)
    {
        $this->catalogues = [];
        foreach ($catalogues as $c) {
            $this->catalogues[$c->getLocale()] = $c;
        }
    }

    /**
     * @return array
     */
    public function getDomains()
    {
        /** @var MessageCatalogue $c */
        $c = reset($this->catalogues);

        return $c->getDomains();
    }

    /**
     * @param string $locale
     * @param string $domain
     *
     * @return Message[]
     */
    public function getMessages($locale, $domain)
    {
        $messages = [];
        if (!isset($this->catalogues[$locale])) {
            return $messages;
        }

        foreach ($this->catalogues[$locale]->all($domain) as $key => $text) {
            $messages[] = new Message($this, $locale, $domain, $key, $text);
        }

        return $messages;
    }

    /**
     * @param string $domain
     * @param string $key
     *
     * @return array
     */
    public function getTranslations($domain, $key)
    {
        $translations = [];
        foreach ($this->catalogues as $locale => $catalogue) {
            if ($catalogue->has($key, $domain)) {
                $translations[$locale] = $catalogue->get($key, $domain);
            }
        }

        return $translations;
    }

    /**
     * @param string $domain
     * @param string $key
     *
     * @return array
     */
    public function getSourceLocations($domain, $key)
    {
        $notes = $this->getNotes($domain, $key);
        $sources = [];
        foreach ($notes as $note) {
            if ($note['content'] === 'file-source') {
                list($path, $line) = explode(':', $note['from'], 2);
                $sources[] = ['full_path' => $this->projectRoot.$path, 'path' => $path, 'line' => $line];
            }
        }

        return $sources;
    }

    /**
     * @param string $domain
     * @param string $key
     *
     * @return bool
     */
    public function isNew($domain, $key)
    {
        $notes = $this->getNotes($domain, $key);
        foreach ($notes as $note) {
            if ($note['content'] === 'status:new') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $domain
     * @param string $key
     *
     * @return bool
     */
    public function isObsolete($domain, $key)
    {
        $notes = $this->getNotes($domain, $key);
        foreach ($notes as $note) {
            if ($note['content'] === 'status:obsolete') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $domain
     * @param $key
     *
     * @return array
     */
    private function getNotes($domain, $key)
    {
        /** @var MessageCatalogue $c */
        $c = reset($this->catalogues);
        $meta = $c->getMetadata($key, $domain);

        if (!isset($meta['notes'])) {
            return [];
        }

        return $meta['notes'];
    }
}
