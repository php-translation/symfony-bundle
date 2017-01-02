<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Tests\Functional;

use Translation\Bundle\Service\ConfigurationManager;

class BundleInitializationTest extends BaseTestCase
{
    public function testRegisterBundle()
    {
        $this->bootKernel();
        $container = $this->getContainer();
        $this->assertTrue($container->has('php_translation.configuration_manager'));
        $config = $container->get('php_translation.configuration_manager');
        $this->assertInstanceOf(ConfigurationManager::class, $config);

        $default = $config->getConfiguration();
        $root = $container->getParameter('kernel.root_dir');
        $this->assertEquals($root.'/Resources/translations', $default->getOutputDir());

        $this->assertTrue($container->has('php_translation.storage'));
    }
}
