<?php

namespace Translation\Bundle\Service;

use Translation\Common\Exception\LogicException;
use Translation\Common\Model\Message;
use Translation\Common\Storage;

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
     * Download all remote storages into all local storages.
     * This will overwrite your local copy.
     */
    public function download()
    {
        // TODO
    }

    /**
     * Upload all local storages into all remote storages
     * This will overwrite your remote copy.
     */
    public function upload()
    {
        // TODO
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
     * Only the local storages will be changed
     */
    public function mergeDown()
    {
        // TODO
    }

    /**
     * Upload and merge all translations from local storages up to your remote storages.
     * Only the remote storages will be changed
     */
    public function mergeUp()
    {
        // TODO
    }


    /**
     * Try to get a translation from all the storages, start looking in the first
     * local storage and then move on to the remote storages.
     * {@inheritdoc}
     */
    public function get($locale, $domain, $key)
    {
        $storageSets = [$this->localStorages, $this->remoteStorages];

        foreach ($storageSets as $set) {
            /** @var Storage $storage */
            foreach ($set as $storage) {
                $value = $storage->get($locale, $domain, $key);
                if (!empty($value)) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * Update all configured storages with this message.
     *
     * {@inheritdoc}
     */
    public function update(Message $message)
    {
        $storageSets = [$this->localStorages, $this->remoteStorages];

        foreach ($storageSets as $set) {
            /** @var Storage $storage */
            foreach ($set as $storage) {
                $storage->update($message);
            }
        }
    }

    /**
     * Delete the message form all storages
     *
     * {@inheritdoc}
     */
    public function delete($locale, $domain, $key)
    {
        $storageSets = [$this->localStorages, $this->remoteStorages];

        foreach ($storageSets as $set) {
            /** @var Storage $storage */
            foreach ($set as $storage) {
                $storage->delete($locale, $domain, $key);
            }
        }
    }

    /**
     * @param Storage $localStorage
     *
     * @return StorageService
     */
    public function addLocalStorages(Storage $localStorage)
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
