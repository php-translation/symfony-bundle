<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Tests\Unit\DependencyInjection;

use Translation\Bundle\DependencyInjection\HappyrMq2phpExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class HappyrMq2phpExtensionTest extends AbstractExtensionTestCase
{
    protected function getMinimalConfiguration()
    {
        $this->setParameter('kernel.bundles', ['SimpleBusAsynchronousBundle' => true]);

        return ['enabled' => true];
    }

    public function testServicesRegisteredAfterLoading()
    {
        $this->load();

        $this->assertContainerBuilderHasService('happyr.mq2php.message_serializer', 'Translation\Bundle\Service\MessageSerializerDecorator');
        $this->assertContainerBuilderHasService('happyr.mq2php.consumer_wrapper', 'Translation\Bundle\Service\ConsumerWrapper');
    }

    protected function getContainerExtensions()
    {
        return [
            new HappyrMq2phpExtension(),
        ];
    }
}
