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
        $kernel = $this->createKernel();
        $request = Request::create('/foobar');
        $response = $kernel->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertNotContains('x-trans', $response->getContent());
    }

    public function testActivatedTest()
    {
        $kernel = $this->createKernel();
        $request = Request::create('/foobar');

        // Activate the feature
        $kernel->boot();
        $kernel->getContainer()->get('php_translation.edit_in_place.activator')->activate();

        $response = $kernel->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertContains('<!-- TranslationBundle -->', $response->getContent());

        $dom = new \DOMDocument();
        @$dom->loadHTML($response->getContent());
        $xpath = new \DomXpath($dom);

        $xtrans = $xpath->query('//x-trans');

        self::assertEquals(5, $xtrans->length);
    }
}
