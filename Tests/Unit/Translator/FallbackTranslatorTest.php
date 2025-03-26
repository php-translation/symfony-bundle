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

use Nyholm\NSA;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface as NewTranslatorInterface;
use Translation\Bundle\Translator\FallbackTranslator;
use Translation\Bundle\Translator\TranslatorInterface;
use Translation\Translator\Translator;
use Translation\Translator\TranslatorService;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class FallbackTranslatorTest extends TestCase
{
    public function testWithNotLocaleAwareTranslator()
    {
        if (!interface_exists(NewTranslatorInterface::class)) {
            $this->markTestSkipped('Relevant only when NewTranslatorInterface is available.');
        }

        $symfonyTranslator = $this->getMockBuilder(NewTranslatorInterface::class)->getMock();
        $translator = new Translator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The given translator must implements LocaleAwareInterface.');

        new FallbackTranslator('en', $symfonyTranslator, $translator);
    }

    public function testTranslateWithSubstitutedParameters(): void
    {
        $symfonyTranslator = $this->getMockBuilder(TranslatorInterface::class)->getMock();

        $translatorService = $this->getMockBuilder(TranslatorService::class)
            ->onlyMethods(['translate'])
            ->getMock();
        $translatorService->method('translate')->willReturnArgument(0);

        $translator = new Translator();
        $translator->addTranslatorService($translatorService);

        $service = new FallbackTranslator('en', $symfonyTranslator, $translator);

        // One parameter test
        $result = NSA::invokeMethod($service, 'translateWithSubstitutedParameters', 'abc bar abc', 'en', ['%foo%' => 'bar']);
        $this->assertEquals('abc bar abc', $result);

        // Two parameters test
        $result = NSA::invokeMethod($service, 'translateWithSubstitutedParameters', 'abc bar abc baz', 'en', ['%foo%' => 'bar', '%biz%' => 'baz']);
        $this->assertEquals('abc bar abc baz', $result);

        // Test with object
        $result = NSA::invokeMethod($service, 'translateWithSubstitutedParameters', 'abc object abc', 'en', ['%foo%' => new Minor('object')]);
        $this->assertEquals('abc object abc', $result);
    }
}

class Minor
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }
}
