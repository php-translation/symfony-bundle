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

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
class EditInPlaceTest extends BaseTestCase
{
    public function testDeactivatedTest()
    {
        $this->bootKernel();
        $request = Request::create('/foobar');
        $response = $this->kernel->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertNotContains('x-trans', $response->getContent());
    }

    public function testActivatedTest()
    {
        $this->bootKernel();
        $request = Request::create('/foobar');

        // Activate the feature
        $this->getContainer()->get('php_translation.edit_in_place.activator')->activate();

        $response = $this->kernel->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertContains('<!-- TranslationBundle -->', $response->getContent());

        $dom = new \DOMDocument('1.0', 'utf-8');
        @$dom->loadHTML(mb_convert_encoding($response->getContent(), 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new \DomXpath($dom);

        // Check number of x-trans tags
        $xtrans = $xpath->query('//x-trans');
        self::assertEquals(6, $xtrans->length);

        // Check attribute with prefix (href="mailto:...")
        $emailTag = $dom->getElementById('email');
        self::assertEquals('mailto:'.'🚫 Can\'t be translated here. 🚫', $emailTag->getAttribute('href'));
        self::assertEquals('localized.email', $emailTag->textContent);

        // Check attribute
        $attributeDiv = $dom->getElementById('attribute-div');
        self::assertEquals('🚫 Can\'t be translated here. 🚫', $attributeDiv->getAttribute('data-value'));
    }
}
