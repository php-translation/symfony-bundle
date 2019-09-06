<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Translation\Bundle\Model\Configuration;
use Translation\Bundle\Service\ConfigurationManager;
use Translation\Bundle\Tests\Unit\Model\ConfigurationTest;

class ConfigurationManagerTest extends TestCase
{
    public function testGetConfigurationFirst()
    {
        $manager = new ConfigurationManager();
        $correctConfiguration = $this->createConfiguration(['name' => 'correct']);

        $manager->addConfiguration('foo', $correctConfiguration);
        $manager->addConfiguration('bar', $this->createConfiguration());

        $this->assertEquals($correctConfiguration, $manager->getConfiguration());
        $this->assertEquals($correctConfiguration, $manager->getConfiguration('default'));
    }

    public function testGetConfigurationDefault()
    {
        $manager = new ConfigurationManager();
        $correctConfiguration = $this->createConfiguration(['name' => 'correct']);

        $manager->addConfiguration('bar', $this->createConfiguration());
        $manager->addConfiguration('default', $correctConfiguration);

        $this->assertEquals($correctConfiguration, $manager->getConfiguration());
        $this->assertEquals($correctConfiguration, $manager->getConfiguration('default'));
        $this->assertEquals($correctConfiguration, $manager->getConfiguration(''));
        $this->assertEquals($correctConfiguration, $manager->getConfiguration(null));
    }

    public function testGetConfigurationMissing()
    {
        $manager = new ConfigurationManager();
        $correctConfiguration = $this->createConfiguration(['name' => 'correct']);

        $manager->addConfiguration('bar', $this->createConfiguration());
        $manager->addConfiguration('default', $correctConfiguration);

        $this->assertNull($manager->getConfiguration('missing'));
    }

    public function testFirstName()
    {
        $manager = new ConfigurationManager();
        $manager->addConfiguration('foo', $this->createConfiguration());
        $manager->addConfiguration('bar', $this->createConfiguration());

        $this->assertEquals('foo', $manager->getFirstName());
    }

    public function testFirstNameEmpty()
    {
        $manager = new ConfigurationManager();

        $this->assertNull($manager->getFirstName());
    }

    public function testGetNames()
    {
        $manager = new ConfigurationManager();
        $manager->addConfiguration('foo', $this->createConfiguration());
        $manager->addConfiguration('bar', $this->createConfiguration());

        $names = $manager->getNames();
        $this->assertEquals(['foo', 'bar'], $names);
    }

    public function testGetNamesEmpty()
    {
        $manager = new ConfigurationManager();

        $names = $manager->getNames();
        $this->assertEquals([], $names);
    }

    private function createConfiguration($data = [])
    {
        $default = ConfigurationTest::getDefaultData();

        return new Configuration(\array_merge($default, $data));
    }
}
