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

use Symfony\Component\Translation\MessageCatalogue;
use Translation\Common\Exception\LogicException;
use Translation\Common\Model\Message;
use Translation\Common\Storage;
use Translation\Common\TransferableStorage;

/**
 * A service that you use to handle the storages.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class StorageService implements Storage
{
    const DIRECTION_UP = 'up';
    const DIRECTION_DOWN = 'down';

    /**
     * @var Storage[]
     */
    private $localStorages = [];

    /**
     * @var Storage[]
     */
    private $remoteStorages = [];

    /**
     * @var CatalogueFetcher
     */
    private $catalogueFetcher;

    /**
     *
     * @param CatalogueFetcher $catalogueFetcher
     */
    public function __construct(CatalogueFetcher $catalogueFetcher)
    {
        $this->catalogueFetcher = $catalogueFetcher;
    }


    /**
     * Download all remote storages into all local storages.
     * This will overwrite your local copy.
     *
     * @param string $configName
     */
    public function download($configName)
    {
        $config = $this->configManager->getConfiguration($configName);
        $locales = $config['locales'];
        foreach ($locales as $locale) {
            $catalogue[$locale] = new MessageCatalogue($locale);
            foreach ($this->remoteStorages as $storage) {
                if ($storage instanceof TransferableStorage) {
                    $storage->export($catalogue[$locale]);
                }
            }
        }

        //TODO write catalogues
    }

    /**
     * Upload all local storages into all remote storages
     * This will overwrite your remote copy.
     *
     * @param string $configName
     */
    public function upload($configName)
    {
        $config = $this->configManager->getConfiguration($configName);
        $transPaths = array_merge($config['external_translations_dirs'], [$config['output_dir']]);

        $catalogues = $this->catalogueFetcher->getCatalogues($config['locales'], $transPaths);
        foreach ($catalogues as $catalogue) {
            foreach ($this->remoteStorages as $storage) {
                if ($storage instanceof TransferableStorage) {
                    $storage->import($catalogue);
                }
            }
        }
    }

    /**
     * Synchronize translations with remote.
     */
    public function sync($direction = self::DIRECTION_DOWN)
    {
        switch ($direction) {
            case self::DIRECTION_DOWN:
                $this->mergeDown();
                $this->mergeUp();
                break;
            case self::DIRECTION_UP:
                $this->mergeUp();
                $this->mergeDown();
                break;
            default:
                throw new LogicException(sprintf('Direction must be either "up" or "down". Value "%s" was provided', $direction));
        }
    }

    /**
     * Download and merge all translations from remote storages down to your local storages.
     * Only the local storages will be changed.
     */
    public function mergeDown()
    {
        // TODO
    }

    /**
     * Upload and merge all translations from local storages up to your remote storages.
     * Only the remote storages will be changed.
     */
    public function mergeUp()
    {
        // TODO
    }

    /**
     * Get the very latest version we know of a message. First look at the remote storage
     * fall back on the local ones.
     *
     * @param string $locale
     * @param string $domain
     * @param string $key
     *
     * @return null|Message
     */
    public function syncAndFetchMessage($locale, $domain, $key)
    {
        $message = $this->getFromStorages($this->remoteStorages, $locale, $domain, $key);
        if (!$message) {
            // If message is not in remote storages
            $message = $this->getFromStorages($this->localStorages, $locale, $domain, $key);
        }

        $this->updateStorages($this->localStorages, $message);

        return $message;
    }

    /**
     * Try to get a translation from all the storages, start looking in the first
     * local storage and then move on to the remote storages.
     * {@inheritdoc}
     */
    public function get($locale, $domain, $key)
    {
        foreach ([$this->localStorages, $this->remoteStorages] as $storages) {
            $value = $this->getFromStorages($storages, $locale, $domain, $key);
            if (!empty($value)) {
                return $value;
            }
        }

        return;
    }

    /**
     * @param Storage[] $storages
     * @param string    $locale
     * @param string    $domain
     * @param string    $key
     *
     * @return null|Message
     */
    private function getFromStorages($storages, $locale, $domain, $key)
    {
        foreach ($storages as $storage) {
            $value = $storage->get($locale, $domain, $key);
            if (!empty($value)) {
                return $value;
            }
        }

        return;
    }

    /**
     * Create all configured storages with this message. This will not overwrite
     * existing message.
     *
     * {@inheritdoc}
     */
    public function create(Message $message)
    {
        foreach ([$this->localStorages, $this->remoteStorages] as $storages) {
            $this->createStorages($storages, $message);
        }
    }

    /**
     * @param Storage[] $storages
     * @param Message   $message
     */
    private function createStorages($storages, Message $message)
    {
        // Validate if message actually has data
        if (empty((array) $message)) {
            return;
        }

        foreach ($storages as $storage) {
            $storage->update($message);
        }
    }

    /**
     * Update all configured storages with this message. If messages does not exist
     * it will be created.
     *
     * {@inheritdoc}
     */
    public function update(Message $message)
    {
        foreach ([$this->localStorages, $this->remoteStorages] as $storages) {
            $this->updateStorages($storages, $message);
        }
    }

    /**
     * @param Storage[] $storages
     * @param Message   $message
     */
    private function updateStorages($storages, Message $message)
    {
        // Validate if message actually has data
        if (empty((array) $message)) {
            return;
        }

        foreach ($storages as $storage) {
            $storage->update($message);
        }
    }

    /**
     * Delete the message form all storages.
     *
     * {@inheritdoc}
     */
    public function delete($locale, $domain, $key)
    {
        foreach ([$this->localStorages, $this->remoteStorages] as $storages) {
            $this->deleteFromStorages($storages, $locale, $domain, $key);
        }
    }

    /**
     * @param Storage[] $storages
     * @param string    $locale
     * @param string    $domain
     * @param string    $key
     */
    private function deleteFromStorages($storages, $locale, $domain, $key)
    {
        foreach ($storages as $storage) {
            $storage->delete($locale, $domain, $key);
        }
    }

    /**
     * @param Storage $localStorage
     *
     * @return StorageService
     */
    public function addLocalStorage(Storage $localStorage)
    {
        $this->localStorages[] = $localStorage;

        return $this;
    }

    /**
     * @param Storage $remoteStorages
     *
     * @return StorageService
     */
    public function addRemoteStorage(Storage $remoteStorage)
    {
        $this->remoteStorages[] = $remoteStorage;

        return $this;
    }
}
