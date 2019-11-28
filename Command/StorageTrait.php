<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Command;

use Translation\Bundle\Service\StorageManager;
use Translation\Bundle\Service\StorageService;

trait StorageTrait
{
    /**
     * @var StorageManager
     */
    private $storageManager;

    /**
     * @param string|string[]|null $configName
     *
     * @throws \InvalidArgumentException
     */
    private function getStorage($configName): StorageService
    {
        if (null === $storage = $this->storageManager->getStorage($configName)) {
            $availableStorages = $this->storageManager->getNames();

            throw new \InvalidArgumentException(\sprintf('Unknown storage "%s". Available storages are "%s".', $configName, \implode('", "', $availableStorages)));
        }

        return $storage;
    }
}
