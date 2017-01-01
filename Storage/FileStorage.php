<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Storage;

use Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Writer\TranslationWriter;
use Translation\Common\Model\Message;
use Translation\Common\Storage;

/**
 * This storage uses Symfony's writer and loader.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class FileStorage implements Storage
{
    /**
     * @var TranslationWriter
     */
    private $writer;

    /**
     * @var TranslationLoader
     */
    private $loader;

    /**
     * @var string directory path
     */
    private $dir;

    /**
     * @var MessageCatalogue[] Fetched catalogies
     */
    private $catalogues;

    /**
     * @param TranslationWriter $writer
     * @param TranslationLoader $loader
     * @param array             $dir
     */
    public function __construct(TranslationWriter $writer, TranslationLoader $loader, $dir)
    {
        $this->writer = $writer;
        $this->loader = $loader;
        $this->dir = $dir;
    }

    /**
     * {@inheritdoc}
     */
    public function get($locale, $domain, $key)
    {
        $catalogue = $this->getCatalogue($locale);

        $translation = $catalogue->get($key, $domain);

        return new Message($key, $domain, $locale, $translation);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Message $m)
    {
        $catalogue = $this->getCatalogue($m->getLocale());
        if (!$catalogue->defines($m->getKey(), $m->getDomain())) {
            $catalogue->set($m->getKey(), $m->getTranslation(), $m->getDomain());
            $this->writeCatalogue($catalogue, $m->getLocale(), $m->getDomain());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(Message $m)
    {
        $catalogue = $this->getCatalogue($m->getLocale());
        $catalogue->set($m->getKey(), $m->getTranslation(), $m->getDomain());
        $this->writeCatalogue($catalogue, $m->getLocale(), $m->getDomain());
    }

    /**
     * {@inheritdoc}
     */
    public function delete($locale, $domain, $key)
    {
        $catalogue = $this->getCatalogue($locale);
        $messages = $catalogue->all($domain);
        unset($messages[$key]);

        $catalogue->replace($messages, $domain);
        $this->writeCatalogue($catalogue, $locale, $domain);
    }

    /**
     * Save catalogue back to file.
     *
     * @param MessageCatalogue $catalogue
     * @param string           $domain
     */
    private function writeCatalogue(MessageCatalogue $catalogue, $locale, $domain)
    {
        $resources = $catalogue->getResources();
        foreach ($resources as $resource) {
            $path = $resource->getResource();
            if (preg_match('|/'.$domain.'\.'.$locale.'\.([a-z]+)$|', $path, $matches)) {
                $this->writer->writeTranslations($catalogue, $matches[1], ['path' => str_replace($matches[0], '', $path)]);
            }
        }
    }

    /**
     * @param string $locale
     *
     * @return MessageCatalogue
     */
    private function getCatalogue($locale)
    {
        if (empty($this->catalogues[$locale])) {
            $this->loadCatalogue($locale, [$this->dir]);
        }

        return $this->catalogues[$locale];
    }

    /**
     * Load catalogue from files.
     *
     * @param string $locale
     * @param array  $dirs
     */
    private function loadCatalogue($locale, array $dirs)
    {
        $currentCatalogue = new MessageCatalogue($locale);
        foreach ($dirs as $path) {
            if (is_dir($path)) {
                $this->loader->loadMessages($path, $currentCatalogue);
            }
        }

        $this->catalogues[$locale] = $currentCatalogue;
    }
}
