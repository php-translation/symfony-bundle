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
use Symfony\Contracts\Translation\TranslatorInterface as NewTranslatorInterface;
use Translation\Bundle\EditInPlace\ActivatorInterface;
use Translation\Bundle\Translator\EditInPlaceTranslator;
use Translation\Bundle\Translator\TranslatorInterface;

/**
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
final class EditInPlaceTranslatorTest extends TestCase
{
    public function testWithNotLocaleAwareTranslator()
    {
        if (!\interface_exists(NewTranslatorInterface::class)) {
            $this->markTestSkipped('Relevant only when NewTranslatorInterface is available.');
        }

        $symfonyTranslator = $this->getMockBuilder(NewTranslatorInterface::class)->getMock();
        $activator = new FakeActivator(true);
        $requestStack = new RequestStack();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The given translator must implements LocaleAwareInterface.');

        new EditInPlaceTranslator($symfonyTranslator, $activator, $requestStack);
    }

    public function testEnabled(): void
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

    public function testDisabled(): void
    {
        $symfonyTranslator = $this->getMockBuilder(TranslatorInterface::class)->getMock();

        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $activator = new FakeActivator(false);
        $service = new EditInPlaceTranslator($symfonyTranslator, $activator, $requestStack);

        $this->assertNull(
            $service->trans('key', [])
        );
    }

    public function testHtmlTranslation(): void
    {
        $symfonyTranslator = new \Symfony\Component\Translation\Translator('en', null, null, true);
        $symfonyTranslator->addLoader('array', new ArrayLoader());
        $symfonyTranslator->addResource('array', ['foo' => 'Normal content.'], 'en');
        $symfonyTranslator->addResource('array', ['bar' => 'Content with <b>HTML</b> in it.'], 'en');
        $symfonyTranslator->addResource('array', ['bar.attr' => 'Content with <b class="alert">HTML</b> in it.'], 'en');

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
            '<x-trans data-key="messages|bar" data-value="Content with &lt;b&gt;HTML&lt;/b&gt; in it." data-plain="Content with &lt;b&gt;HTML&lt;/b&gt; in it." data-domain="messages" data-locale="en">Content with <b>HTML</b> in it.</x-trans>',
            $service->trans('bar', [])
        );

        $this->assertSame(
            '<x-trans data-key="messages|bar.attr" data-value="Content with &lt;b class=&quot;alert&quot;&gt;HTML&lt;/b&gt; in it." data-plain="Content with &lt;b class=&quot;alert&quot;&gt;HTML&lt;/b&gt; in it." data-domain="messages" data-locale="en">Content with <b class="alert">HTML</b> in it.</x-trans>',
            $service->trans('bar.attr', [])
        );
    }
}

class FakeActivator implements ActivatorInterface
{
    private $enabled;

    public function __construct(bool $enabled = true)
    {
        $this->enabled = $enabled;
    }

    public function checkRequest(Request $request = null): bool
    {
        return $this->enabled;
    }
}
