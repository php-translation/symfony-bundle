<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Tests\Functional\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Translation\Bundle\Tests\Functional\BaseTestCase;

class SyncCommandTest extends BaseTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->kernel->addConfigFile(__DIR__.'/../app/config/normal_config.yml');

        file_put_contents(__DIR__.'/../app/Resources/translations/messages.sv.xlf', <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="fr-FR" trgLang="en-US">
    <file id="messages.en_US">
    <unit id="xx1">
      <segment>
        <source>translated.heading</source>
        <target>My translated heading</target>
      </segment>
    </unit>
    <unit id="xx2">
      <segment>
        <source>translated.paragraph0</source>
        <target>My translated paragraph0</target>
      </segment>
    </unit>
    <unit id="xx3">
      <notes>
        <note category="file-source" priority="1">foobar.html.twig:9</note>
      </notes>
      <segment>
        <source>translated.paragraph1</source>
        <target>My translated paragraph1</target>
      </segment>
    </unit>
    <unit id="xx4">
      <segment>
        <source>not.in.source</source>
        <target>This is not in the source code</target>
      </segment>
    </unit>
    </file>
</xliff>

XML
        );
    }

    public function testExecute()
    {
        $this->bootKernel();
        $application = new Application($this->kernel);

        $container = $this->getContainer();
        $application->add($container->get('php_translator.console.sync'));

        $command = $application->find('translation:sync');
        $commandTester = new CommandTester($command);

        try {
            $commandTester->execute([
                'command' => $command->getName(),
                'configuration' => 'fail',
            ]);

            $this->fail('The command should fail when called with an unknown configuration key.');
        } catch (\InvalidArgumentException $e) {
            $this->assertRegExp('|Unknown storage "fail"\.|s', $e->getMessage());
            $this->assertRegExp('|Available storages are "app"\.|s', $e->getMessage());
        }
    }
}
