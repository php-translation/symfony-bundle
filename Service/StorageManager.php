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

/**
 * A service to easily access different storage services.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class StorageManager
{
    /**
     * @var StorageService[]
     */
    private $storages = [];

    public function addStorage(string $name, StorageService $storage): void
    {
        $this->storages[$name] = $storage;
    }

    /**
     * @param string|string[]|null $name
     */
    public function getStorage($name = null): ?StorageService
    {
        if (empty($name)) {
            return $this->getStorage('default');
        }

        if (isset($this->storages[$name])) {
            return $this->storages[$name];
        }

        if ('default' === $name) {
            $name = $this->getFirstName();
            if (isset($this->storages[$name])) {
                return $this->storages[$name];
            }
        }

        return null;
    }

    public function getFirstName(): ?string
    {
        foreach ($this->storages as $name => $config) {
            return $name;
        }

        return null;
    }

    public function getNames(): array
    {
        return \array_keys($this->storages);
    }
}
