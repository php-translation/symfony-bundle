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

use Translation\Bundle\Service\MessageSerializerDecorator;

class MessageSerializerDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function testWrapAndSerialize()
    {
        $inner = $this->getMockBuilder('SimpleBus\Serialization\Envelope\Serializer\MessageInEnvelopSerializer')
            ->getMock();
        $inner->method('wrapAndSerialize')
            ->willReturnArgument(0);

        $eventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->getMock();

        $service = new MessageSerializerDecorator($inner, $eventDispatcher, ['foo' => 'bar', 'baz' => 'biz']);
        $result = $service->wrapAndSerialize('data');

        $array = json_decode($result, true);
        $this->assertEquals('foo', $array['headers'][0]['key']);
        $this->assertEquals('bar', $array['headers'][0]['value']);
        $this->assertEquals('baz', $array['headers'][1]['key']);
        $this->assertEquals('biz', $array['headers'][1]['value']);
        $this->assertEquals('data', $array['body']);
    }
}
