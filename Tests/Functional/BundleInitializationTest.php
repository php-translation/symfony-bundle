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

use Translation\Bundle\Catalogue\CatalogueFetcher;
use Translation\Bundle\Catalogue\CatalogueManager;
use Translation\Bundle\Catalogue\CatalogueWriter;
use Translation\Bundle\Service\ConfigurationManager;
use Translation\Bundle\Service\StorageService;

class BundleInitializationTest extends BaseTestCase
{
    public function testRegisterBundle(): void
    {
        $kernel = $this->testKernel;
        $kernel->boot();
        $container = $kernel->getContainer();

        $this->assertTrue($container->has(ConfigurationManager::class));
        $config = $container->get(ConfigurationManager::class);
        $this->assertInstanceOf(ConfigurationManager::class, $config);

        $default = $config->getConfiguration();
        $root = $container->getParameter('kernel.project_dir');
        $this->assertEquals($root.'/Resources/translations', $default->getOutputDir());

        $services = [
            CatalogueFetcher::class,
            CatalogueManager::class,
            CatalogueWriter::class,
            'php_translation.storage' => StorageService::class,
        ];

        foreach ($services as $id => $class) {
            $id = \is_int($id) ? $class : $id;

            $this->assertTrue($container->has($id));
            $s = $container->get($id);
            $this->assertInstanceOf($class, $s);
        }
    }
}
