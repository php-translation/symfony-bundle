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
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ConfigurationManager
{
    /**
     * @var Configuration[]
     */
    private $configuration = [];

    /**
     * @param string $name
     * @param Configuration $configuration
     */
    public function addConfiguration($name, Configuration $configuration)
    {
        $this->configuration[$name] = $configuration;
    }

    /**
     * @param string $name
     *
     * @return null|Configuration
     */
    public function getConfiguration($name = 'default')
    {
        if (!isset($this->configuration[$name])) {
            if ($name === 'default') {
                return $this->getConfiguration($this->getFirstName());
            }

            return null;
        }

        return $this->configuration[$name];
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        foreach ($this->configuration as $name => $config) {
            return $name;
        }
    }

    /**
     * @return array
     */
    public function getNames()
    {
        return array_keys($this->configuration);
    }
}
