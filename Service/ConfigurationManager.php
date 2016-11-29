<?php

namespace Translation\Bundle\Service;

/**
 *
 *
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
     *
     * @return string
     */
    public function getFirstName()
    {
        foreach ($this->configuration as $name => $config) {
            return $name;
        }
    }
}
