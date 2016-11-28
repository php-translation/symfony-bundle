<?php

namespace Translation\Bundle\Tests\Functional;

use Translation\Bundle\Service\ConsumerWrapper;
use Translation\Bundle\Service\MessageSerializerDecorator;

class BundleInitializationTest extends BaseTestCase
{
    public function testRegisterBundle()
    {
        static::bootKernel();
        $container = static::$kernel->getContainer();
        $this->assertTrue($container->has('happyr.mq2php.message_serializer'));
        $client = $container->get('happyr.mq2php.message_serializer');
        $this->assertInstanceOf(MessageSerializerDecorator::class, $client);

        $this->assertTrue($container->has('happyr.mq2php.consumer_wrapper'));
        $client = $container->get('happyr.mq2php.consumer_wrapper');
        $this->assertInstanceOf(ConsumerWrapper::class, $client);
    }
}
