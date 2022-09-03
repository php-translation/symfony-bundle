<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Request;
use Translation\Bundle\Tests\Functional\BaseTestCase;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class EditInPlaceControllerTest extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        file_put_contents(__DIR__.'/../app/Resources/translations/messages.sv.xlf', <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="fr-FR" trgLang="en-US">
    <file id="messages.en_US">
        <unit id="LCa0a2j">
            <segment>
                <source>key0</source>
                <target>trans0</target>
            </segment>
        </unit>
        <unit id="LCa0a2b">
            <segment>
                <source>key1</source>
                <target>trans1</target>
            </segment>
        </unit>
    </file>
</xliff>

XML
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->testKernel->addTestConfig(__DIR__.'/../app/config/normal_config.yaml');
    }

    public function testEditAction(): void
    {
        $request = Request::create('/admin/_trans_edit_in_place/app/sv', 'POST', [], [], [], [], json_encode([
            'messages|key0' => 'trans0',
            'messages|key1' => 'trans1',
        ]));
        $response = $this->testKernel->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEditActionError(): void
    {
        $request = Request::create('/admin/_trans_edit_in_place/app/sv', 'POST', [], [], [], [], json_encode([
            'messages|key0' => 'trans0',
            'messages|' => 'trans1',
        ]));
        $response = $this->testKernel->handle($request);
        $this->assertEquals(400, $response->getStatusCode());
    }
}
