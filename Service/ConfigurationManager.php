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
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ConfigurationManager
{
    /**
     * @var array name => config
     */
    private $configuration = [];

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return array
     */
    public function getConfiguration($name)
    {
        if (!isset($this->configuration[$name])) {
            return [];
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
