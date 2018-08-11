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
    private $configurations = [];

    /**
     * @param string        $name
     * @param Configuration $configuration
     */
    public function addConfiguration($name, Configuration $configuration)
    {
        $this->configurations[$name] = $configuration;
    }

    /**
     * @param null|string $name
     *
     * @return null|Configuration
     */
    public function getConfiguration($name = null)
    {
        if (empty($name)) {
            return $this->getConfiguration('default');
        }

        if (isset($this->configurations[$name])) {
            return $this->configurations[$name];
        }

        if ('default' === $name) {
            $name = $this->getFirstName();
            if (isset($this->configurations[$name])) {
                return $this->configurations[$name];
            }
        }
    }

    /**
     * @param null|string $domain
     *
     * @return null|Configuration
     */
    public function getConfigurationByDomain($domain = null)
    {
        if (empty($domain)) {
            return $this->getConfiguration('default');
        }

        foreach($this->configurations as $configuration) {
            if ($configuration->hasDomain($domain)) {
                return $configuration;
            }
        }

        return $this->getConfiguration('default');
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        foreach ($this->configurations as $name => $config) {
            return $name;
        }
    }

    /**
     * @return array
     */
    public function getNames()
    {
        return array_keys($this->configurations);
    }
}
