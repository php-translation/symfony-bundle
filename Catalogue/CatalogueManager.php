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
    private $catalogues;

    /**
     * @var string
     */
    private $projectRoot;

    /**
     * @var NoteParser
     */
    private $noteParser;

    /**
     * @param string     $projectRoot
     * @param NoteParser $noteParser
     */
    public function __construct($projectRoot, NoteParser $noteParser)
    {
        $this->projectRoot = $projectRoot;
        $this->noteParser = $noteParser;
    }

    /**
     * @param MessageCatalogueInterface[] $catalogues
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
        /** @var MessageCatalogueInterface $c */
        $c = reset($this->catalogues);

        return $c->getDomains();
    }

    /**
     * @param string $locale
     * @param string $domain
     *
     * @return CatalogueMessage[]
     */
    public function getMessages($locale, $domain)
    {
        $messages = [];
        if (!isset($this->catalogues[$locale])) {
            return $messages;
        }

        foreach ($this->catalogues[$locale]->all($domain) as $key => $text) {
            $messages[] = new CatalogueMessage($this, $locale, $domain, $key, $text);
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
     * }
     *
     * @return CatalogueMessage[]
     */
    public function findMessages(array $config = [])
    {
        $inputDomain = isset($config['domain']) ? $config['domain'] : null;
        $isNew = isset($config['isNew']) ? $config['isNew'] : null;
        $isObsolete = isset($config['isObsolete']) ? $config['isObsolete'] : null;

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
                    // Filter on new and obsolete
                    if (null !== $isNew || null !== $isObsolete) {
                        $notes = $this->getNotes($domain, $key, $catalogue);

                        if (null !== $isNew) {
                            if ($isNew !== $this->noteParser->hasNoteNew($notes)) {
                                continue;
                            }
                        }
                        if (null !== $isObsolete) {
                            if ($isObsolete !== $this->noteParser->hasNoteObsolete($notes)) {
                                continue;
                            }
                        }
                    }

                    $messages[] = new CatalogueMessage($this, $locale, $domain, $key, $text);
                }
            }
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

        return $this->noteParser->hasNoteNew($notes);
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

        return $this->noteParser->hasNoteObsolete($notes);
    }

    /**
     * @param $domain
     * @param $key
     * @param MessageCatalogueInterface|null $catalogue
     *
     * @return array
     */
    private function getNotes($domain, $key, MessageCatalogueInterface $catalogue = null)
    {
        if (null === $catalogue) {
            /** @var MessageCatalogueInterface $c */
            $catalogue = reset($this->catalogues);
        }

        if (!$catalogue instanceof MetadataAwareInterface) {
            return [];
        }

        return $this->noteParser->getNotes($domain, $key, $catalogue);
    }
}
