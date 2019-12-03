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

use Translation\Bundle\Model\Configuration;

/**
 * A service to easily access different configurations.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class ConfigurationManager
{
    /**
     * @var Configuration[]
     */
    private $configuration = [];

    public function addConfiguration(string $name, Configuration $configuration): void
    {
        $this->configuration[$name] = $configuration;
    }

    /**
     * @param string|string[]|null $name
     */
    public function getConfiguration($name = null): Configuration
    {
        if (empty($name)) {
            return $this->getConfiguration('default');
        }

        if (isset($this->configuration[$name])) {
            return $this->configuration[$name];
        }

        if ('default' === $name) {
            $name = $this->getFirstName();
            if (isset($this->configuration[$name])) {
                return $this->configuration[$name];
            }
        }

        throw new \InvalidArgumentException(\sprintf('No configuration found for "%s"', $name));
    }

    public function getFirstName(): ?string
    {
        foreach ($this->configuration as $name => $config) {
            return $name;
        }

        return null;
    }

    public function getNames(): array
    {
        return \array_keys($this->configuration);
    }
}
