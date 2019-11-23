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

use Nyholm\BundleTest\AppKernel;
use Nyholm\BundleTest\BaseBundleTestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Translation\Bundle\TranslationBundle;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class BaseTestCase extends BaseBundleTestCase
{
    /**
     * @var AppKernel
     */
    protected $kernel;

    protected function getBundleClass()
    {
        return FrameworkBundle::class;
    }

    protected function setUp(): void
    {
        $kernel = $this->createKernel();
        $kernel->addConfigFile(__DIR__.'/app/config/default.yml');

        $kernel->addBundle(TwigBundle::class);
        $kernel->addBundle(TranslationBundle::class);

        $this->kernel = $kernel;

        parent::setUp();
    }
}
