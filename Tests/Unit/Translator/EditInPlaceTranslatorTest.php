<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Tests\Unit\Translator;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\TranslatorInterface;
use Translation\Bundle\EditInPlace\ActivatorInterface;
use Translation\Bundle\Translator\EditInPlaceTranslator;

/**
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
final class EditInPlaceTranslatorTest extends TestCase
{
    public function testEnabled()
    {
        $symfonyTranslator = $this->getMockBuilder(TranslatorInterface::class)->getMock();

        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $activator = new FakeActivator(true);
        $service = new EditInPlaceTranslator($symfonyTranslator, $activator, $requestStack);

        $this->assertSame(
            '<x-trans data-key="messages|key" data-value="" data-plain="" data-domain="messages" data-locale=""></x-trans>',
            $service->trans('key', [])
        );
    }

    public function testDisabled()
    {
        $symfonyTranslator = $this->getMockBuilder(TranslatorInterface::class)->getMock();

        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $activator = new FakeActivator(false);
        $service = new EditInPlaceTranslator($symfonyTranslator, $activator, $requestStack);

        $this->assertSame(
            null,
            $service->trans('key', [])
        );
    }

    public function testHtmlTranslation()
    {
        $symfonyTranslator = new \Symfony\Component\Translation\Translator('en', null, null, true);
        $symfonyTranslator->addLoader('array', new ArrayLoader());
        $symfonyTranslator->addResource('array', ['foo' => 'Normal content.'], 'en');
        $symfonyTranslator->addResource('array', ['bar' => 'Content with <b>HTML</b> in it.'], 'en');

        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $activator = new FakeActivator(true);
        $service = new EditInPlaceTranslator($symfonyTranslator, $activator, $requestStack);

        $this->assertSame(
            '<x-trans data-key="messages|foo" data-value="Normal content." data-plain="Normal content." data-domain="messages" data-locale="en">Normal content.</x-trans>',
            $service->trans('foo', [])
        );

        $this->assertSame(
            'Content with <b>HTML</b> in it.',
            $service->trans('bar', [])
        );
    }
}

class FakeActivator implements ActivatorInterface
{
    private $enabled;

    public function __construct($enabled = true)
    {
        $this->enabled = $enabled;
    }

    public function checkRequest(Request $request = null)
    {
        return $this->enabled;
    }
}
