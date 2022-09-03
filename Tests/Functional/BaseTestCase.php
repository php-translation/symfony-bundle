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

use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\HttpKernel\Kernel;
use Translation\Bundle\TranslationBundle;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class BaseTestCase extends KernelTestCase
{
    /**
     * @var TestKernel
     */
    protected $testKernel;

    protected function getBundleClass(): string
    {
        return FrameworkBundle::class;
    }

    protected function setUp(): void
    {
        $kernel = self::createKernel();

        if (Kernel::VERSION_ID < 50300) {
            $kernel->addTestConfig(__DIR__.'/app/config/default_legacy.yaml');
        } else {
            $kernel->addTestConfig(__DIR__.'/app/config/default.yaml');
        }

        $kernel->addTestBundle(TwigBundle::class);
        $kernel->addTestBundle(TranslationBundle::class);

        $this->testKernel = $kernel;

        parent::setUp();
    }
}
