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
use Translation\Extractor\Extractor;
use Translation\Extractor\FileExtractor\PHPFileExtractor;
use Translation\Extractor\FileExtractor\TwigFileExtractor;

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

        $services = [
            'php_translation.storage' => StorageService::class,
            'test.php_translation.extractor.twig' => TwigFileExtractor::class,
            'test.php_translation.extractor.php' => PHPFileExtractor::class,
            'php_translation.catalogue_fetcher' => CatalogueFetcher::class,
            'php_translation.catalogue_writer' => CatalogueWriter::class,
            'php_translation.catalogue_manager' => CatalogueManager::class,
            'php_translation.extractor' => Extractor::class,
        ];

        foreach ($services as $id => $class) {
            $this->assertTrue($container->has($id));
            $s = $container->get($id);
            $this->assertInstanceOf($class, $s);
        }
    }
}
