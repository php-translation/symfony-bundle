<?php
declare(strict_types=1);

namespace Translation\Bundle\Tests\Functional\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Translation\Bundle\Tests\Functional\BaseTestCase;

class CheckCommandTest extends BaseTestCase
{
    /**
     * @var Application
     */
    private $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel->addConfigFile(__DIR__.'/../app/config/normal_config.yaml');
        $this->bootKernel();
        $this->application = new Application($this->kernel);

        \file_put_contents(__DIR__.'/../app/Resources/translations/messages.sv.xlf', <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="en" trgLang="sv">
  <file id="messages.sv">
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

    public function testReportsMissingTranslations(): void
    {
        $commandTester = new CommandTester($this->application->find('translation:check'));

        $commandTester->execute(['locale' => 'sv', 'configuration' => 'app']);

        $this->assertStringContainsString(
            '4 new message(s) have been found, run bin/console translation:extract',
            $commandTester->getDisplay()
        );
        $this->assertGreaterThan(0, $commandTester->getStatusCode());
    }

    public function testReportsNoNewTranslationMessages(): void
    {
        // run translation:extract first, so all translations are extracted
        (new CommandTester($this->application->find('translation:extract')))->execute(['locale' => 'sv']);

        $commandTester = new CommandTester($this->application->find('translation:check'));

        $commandTester->execute(['locale' => 'sv', 'configuration' => 'app']);

        $this->assertStringContainsString(
            'No new translation messages',
            $commandTester->getDisplay()
        );
        $this->assertSame(0, $commandTester->getStatusCode());
    }
}
