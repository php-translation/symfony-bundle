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

use Symfony\Component\DependencyInjection\ResettableContainerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Translation\Bundle\Tests\Functional\app\AppKernel;

/**
 *
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    private static $cachePrefix;

    /**
     * @var AppKernel
     */
    private $kernel;

    /**
     * Boots the Kernel for this test.
     *
     * @param array $options
     */
    protected function bootKernel()
    {
        $this->ensureKernelShutdown();

        if (null === $this->kernel) {
            $this->createKernel();
        }

        $this->kernel->boot();
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /**
     * Get a kernel which you may configure with your bundle and services.
     *
     * @return AppKernel
     */
    protected function createKernel($options = [])
    {
        if (!class_exists(Kernel::class)) {
            throw new \LogicException('You must install symfony/symfony to run the bundle test.');
        }

        require_once __DIR__.'/app/AppKernel.php';
        $class = 'Translation\Bundle\Tests\Functional\app\AppKernel';

        if (!self::$cachePrefix) {
            self::$cachePrefix = uniqid('cache');
        }

        $this->kernel = new $class(self::$cachePrefix, isset($options['config']) ? $options['config'] : 'default.yml');

        return $this->kernel;
    }

    /**
     * Shuts the kernel down if it was used in the test.
     */
    private function ensureKernelShutdown()
    {
        if (null !== $this->kernel) {
            $container = $this->kernel->getContainer();
            $this->kernel->shutdown();
            if ($container instanceof ResettableContainerInterface) {
                $container->reset();
            }
        }
    }

    /**
     * Clean up Kernel usage in this test.
     */
    protected function tearDown()
    {
        $this->ensureKernelShutdown();
    }
}
