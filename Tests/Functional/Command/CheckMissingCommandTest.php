<?php

declare(strict_types=1);

namespace Translation\Bundle\Tests\Functional\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Translation\Bundle\Tests\Functional\BaseTestCase;

class CheckMissingCommandTest extends BaseTestCase
{
    /**
     * @var Application
     */
    private $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testKernel->addTestConfig(__DIR__.'/../app/config/normal_config.yaml');
        $this->testKernel->boot();
        $this->application = new Application($this->testKernel);

        file_put_contents(
            __DIR__.'/../app/Resources/translations/messages.sv.xlf',
            <<<'XML'
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
        $commandTester = new CommandTester($this->application->find('translation:check-missing'));

        $commandTester->execute(['locale' => 'sv', 'configuration' => 'app']);

        $this->assertStringContainsString(
            '4 new message(s) have been found, run bin/console translation:extract',
            $commandTester->getDisplay()
        );
        $this->assertGreaterThan(0, $commandTester->getStatusCode());
    }

    public function testReportsEmptyTranslationMessages(): void
    {
        // run translation:extract first, so all translations are extracted
        (new CommandTester($this->application->find('translation:extract')))->execute(['locale' => 'sv']);

        $commandTester = new CommandTester($this->application->find('translation:check-missing'));

        $commandTester->execute(['locale' => 'sv', 'configuration' => 'app']);

        $this->assertStringContainsString(
            '4 messages have empty translations, please provide translations',
            $commandTester->getDisplay()
        );
        $this->assertGreaterThan(0, $commandTester->getStatusCode());
    }

    public function testReportsNoNewTranslationMessages(): void
    {
        file_put_contents(
            __DIR__.'/../app/Resources/translations/messages.sv.xlf',
            <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="en" trgLang="sv">
  <file id="messages.sv">
    <unit id="gwCXP88" name="translated.title">
      <notes>
        <note category="file-source" priority="1">Resources/views/translated.html.twig:5</note>
        <note category="state" priority="1">new</note>
      </notes>
      <segment>
        <source>translated.title</source>
        <target>My translated title</target>
      </segment>
    </unit>
    <unit id="MVOZYWq" name="translated.heading">
      <notes>
        <note category="file-source" priority="1">Resources/views/translated.html.twig:8</note>
      </notes>
      <segment>
        <source>translated.heading</source>
        <target>My translated heading</target>
      </segment>
    </unit>
    <unit id="bJFCP77" name="translated.paragraph0">
      <notes>
        <note category="file-source" priority="1">Resources/views/translated.html.twig:9</note>
      </notes>
      <segment>
        <source>translated.paragraph0</source>
        <target>My translated paragraph0</target>
      </segment>
    </unit>
    <unit id="1QAmWwr" name="translated.paragraph1">
      <notes>
        <note category="file-source" priority="1">Resources/views/translated.html.twig:9</note>
      </notes>
      <segment>
        <source>translated.paragraph1</source>
        <target>My translated paragraph1</target>
      </segment>
    </unit>
    <unit id="7AdXS54" name="translated.paragraph2">
      <notes>
        <note category="file-source" priority="1">Resources/views/translated.html.twig:11</note>
        <note category="state" priority="1">new</note>
      </notes>
      <segment>
        <source>translated.paragraph2</source>
        <target>My translated paragraph2</target>
      </segment>
    </unit>
    <unit id="WvnvT8X" name="localized.email">
      <notes>
        <note category="file-source" priority="1">Resources/views/translated.html.twig:12</note>
        <note category="file-source" priority="1">Resources/views/translated.html.twig:12</note>
        <note category="state" priority="1">new</note>
      </notes>
      <segment>
        <source>localized.email</source>
        <target>My localized email</target>
      </segment>
    </unit>
    <unit id="ETjQiEP" name="translated.attribute">
      <notes>
        <note category="file-source" priority="1">Resources/views/translated.html.twig:14</note>
        <note category="state" priority="1">new</note>
      </notes>
      <segment>
        <source>translated.attribute</source>
        <target>My translated attribute</target>
      </segment>
    </unit>
    <unit id="GO15Lkx" name="not.in.source">
      <notes>
        <note category="state" priority="1">obsolete</note>
      </notes>
      <segment>
        <source>not.in.source</source>
        <target>This is not in the source code</target>
      </segment>
    </unit>
  </file>
</xliff>
XML
        );

        $commandTester = new CommandTester($this->application->find('translation:check-missing'));

        $commandTester->execute(['locale' => 'sv', 'configuration' => 'app']);

        $this->assertStringContainsString(
            'No new translation messages',
            $commandTester->getDisplay()
        );
        $this->assertSame(0, $commandTester->getStatusCode());
    }
}
