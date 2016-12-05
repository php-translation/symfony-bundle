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
use Translation\Common\Exception\StorageException;
use Translation\Common\Storage;

/**
 * This storage uses Symfony's writer and loader.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class FileStorage implements Storage
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

    public function set($locale, $domain, $key, $message)
    {
        $originalMessage = $this->get($locale, $domain, $key);
        if (!empty($originalMessage)) {
            throw StorageException::translationExists($key, $domain);
        }

        $catalogue = $this->getCatalogue($locale);
        $catalogue->set($key, $message, $domain);
    }

    public function get($locale, $domain, $key)
    {
        $catalogue = $this->getCatalogue($locale);

        return $catalogue->get($key, $domain);
    }

    public function update($locale, $domain, $key, $message)
    {
        $catalogue = $this->getCatalogue($locale);
        $catalogue->set($key, $message, $domain);
        $this->writeCatalogue($catalogue, $locale, $domain);
    }

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
     * @param $locale
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
     * @param $locale
     * @param array $dirs
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
