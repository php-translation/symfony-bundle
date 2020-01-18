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
use Translation\Bundle\Catalogue\CatalogueFetcher;
use Translation\Bundle\Model\Configuration;
use Translation\Common\Exception\LogicException;
use Translation\Common\Model\Message;
use Translation\Common\Model\MessageInterface;
use Translation\Common\Storage;
use Translation\Common\TransferableStorage;

/**
 * A service that you use to handle the storages.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class StorageService implements Storage
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

    private $catalogueFetcher;
    private $config;

    public function __construct(CatalogueFetcher $catalogueFetcher, Configuration $config)
    {
        $this->catalogueFetcher = $catalogueFetcher;
        $this->config = $config;
    }

    /**
     * Download catalogues from all storages.
     *
     * @return MessageCatalogue[]
     */
    public function download(array $exportOptions = []): array
    {
        $catalogues = [];
        foreach ($this->config->getLocales() as $locale) {
            $catalogues[$locale] = new MessageCatalogue($locale);
            foreach ($this->remoteStorages as $storage) {
                if ($storage instanceof TransferableStorage) {
                    $storage->export($catalogues[$locale], $exportOptions);
                }
            }
        }

        return $catalogues;
    }

    /**
     * Synchronize translations with remote.
     */
    public function sync(string $direction = self::DIRECTION_DOWN, array $importOptions = [], array $exportOptions = []): void
    {
        switch ($direction) {
            case self::DIRECTION_DOWN:
                $this->mergeDown($exportOptions);
                $this->mergeUp($importOptions);

                break;
            case self::DIRECTION_UP:
                $this->mergeUp($importOptions);
                $this->mergeDown($exportOptions);

                break;
            default:
                throw new LogicException(\sprintf('Direction must be either "up" or "down". Value "%s" was provided', $direction));
        }
    }

    /**
     * Download and merge all translations from remote storages down to your local storages.
     * Only the local storages will be changed.
     */
    public function mergeDown(array $exportOptions = []): void
    {
        $catalogues = $this->download($exportOptions);

        foreach ($catalogues as $locale => $catalogue) {
            foreach ($catalogue->all() as $domain => $messages) {
                foreach ($messages as $key => $translation) {
                    $message = new Message($key, $domain, $locale, $translation);
                    $this->updateStorages($this->localStorages, $message);
                }
            }
        }
    }

    /**
     * Upload and merge all translations from local storages up to your remote storages.
     * Only the remote storages will be changed.
     *
     * This will overwrite your remote copy.
     */
    public function mergeUp(array $importOptions = []): void
    {
        $catalogues = $this->catalogueFetcher->getCatalogues($this->config);
        foreach ($catalogues as $catalogue) {
            foreach ($this->remoteStorages as $storage) {
                if ($storage instanceof TransferableStorage) {
                    $storage->import($catalogue, $importOptions);
                }
            }
        }
    }

    /**
     * Get the very latest version we know of a message. First look at the remote storage
     * fall back on the local ones.
     */
    public function syncAndFetchMessage(string $locale, string $domain, string $key): ?Message
    {
        if (null === $message = $this->getFromStorages($this->remoteStorages, $locale, $domain, $key)) {
            // If message is not in remote storages, try local
            $message = $this->getFromStorages($this->localStorages, $locale, $domain, $key);
        }

        if (!$message) {
            return null;
        }

        $this->updateStorages($this->localStorages, $message);

        return $message;
    }

    /**
     * Try to get a translation from all the storages, start looking in the first
     * local storage and then move on to the remote storages.
     * {@inheritdoc}
     */
    public function get(string $locale, string $domain, string $key): ?MessageInterface
    {
        foreach ([$this->localStorages, $this->remoteStorages] as $storages) {
            $value = $this->getFromStorages($storages, $locale, $domain, $key);
            if (null !== $value) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param Storage[] $storages
     */
    private function getFromStorages(array $storages, string $locale, string $domain, string $key): ?Message
    {
        foreach ($storages as $storage) {
            $value = $storage->get($locale, $domain, $key);
            if (null !== $value) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Create all configured storages with this message. This will not overwrite
     * existing message.
     *
     * {@inheritdoc}
     */
    public function create(MessageInterface $message): void
    {
        // Validate if message actually has data
        if (empty((array) $message)) {
            return;
        }

        foreach ([$this->localStorages, $this->remoteStorages] as $storages) {
            /** @var Storage $storage */
            foreach ($storages as $storage) {
                $storage->create($message);
            }
        }
    }

    /**
     * Update all configured storages with this message. If messages does not exist
     * it will be created.
     *
     * {@inheritdoc}
     */
    public function update(MessageInterface $message): void
    {
        foreach ([$this->localStorages, $this->remoteStorages] as $storages) {
            $this->updateStorages($storages, $message);
        }
    }

    /**
     * @param Storage[] $storages
     */
    private function updateStorages(array $storages, MessageInterface $message): void
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
    public function delete(string $locale, string $domain, string $key): void
    {
        foreach ([$this->localStorages, $this->remoteStorages] as $storages) {
            /** @var Storage $storage */
            foreach ($storages as $storage) {
                $storage->delete($locale, $domain, $key);
            }
        }
    }

    public function addLocalStorage(Storage $localStorage): self
    {
        $this->localStorages[] = $localStorage;

        return $this;
    }

    public function addRemoteStorage(Storage $remoteStorage): self
    {
        $this->remoteStorages[] = $remoteStorage;

        return $this;
    }
}
