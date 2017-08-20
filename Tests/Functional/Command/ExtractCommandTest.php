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
use Translation\Bundle\Command\ExtractCommand;
use Translation\Bundle\Tests\Functional\BaseTestCase;

class ExtractCommandTest extends BaseTestCase
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
        $application = new Application($this->kernel);

        $application->add(new ExtractCommand());

        $command = $application->find('translation:extract');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            'configuration' => 'app',
            'locale' => 'sv',
        ));

        // the output of the command in the console
        $output = $commandTester->getDisplay();

        // Make sure we have 4 new messages
        $this->assertRegExp('|New messages +4|s', $output);
        $this->assertRegExp('|Total defined messages +8|s', $output);

        $container = $this->getContainer();
        $config = $container->get('php_translation.configuration_manager')->getConfiguration('app');
        $catalogues = $container->get('php_translation.catalogue_fetcher')->getCatalogues($config, ['sv']);

        $this->assertEquals('My translated heading', $catalogues[0]->get('translated.heading'));
    }
}
