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
use Symfony\Component\HttpKernel\Kernel;
use Translation\Bundle\Catalogue\CatalogueFetcher;
use Translation\Bundle\Command\ExtractCommand;
use Translation\Bundle\Model\Metadata;
use Translation\Bundle\Service\ConfigurationManager;
use Translation\Bundle\Tests\Functional\BaseTestCase;

class ExtractCommandTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->testKernel->addTestConfig(__DIR__.'/../app/config/normal_config.yaml');

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

    public function testExecute(): void
    {
        $this->testKernel->boot();
        $application = new Application($this->testKernel);

        $container = $this->testKernel->getContainer();
        $application->add($container->get(ExtractCommand::class));

        // transchoice tag have been definively removed in sf ^5.0
        // Remove this condition & views_with_transchoice + associated config once sf ^5.0 is the minimum supported version.
        if (version_compare(Kernel::VERSION, 5.0, '<')) {
            $configuration = 'app_with_transchoice';
        } else {
            $configuration = 'app';
        }

        $command = $application->find('translation:extract');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'configuration' => $configuration,
            'locale' => 'sv',
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();

        // Make sure we have 4 new messages
        $this->assertMatchesRegularExpression('|New messages +4|s', $output);
        $this->assertMatchesRegularExpression('|Total defined messages +8|s', $output);

        $config = $container->get(ConfigurationManager::class)->getConfiguration('app');
        $catalogues = $container->get(CatalogueFetcher::class)->getCatalogues($config, ['sv']);

        $catalogue = $catalogues[0];
        $this->assertEquals('My translated heading', $catalogue->get('translated.heading'), 'Translated strings MUST NOT disappear.');

        // Test meta, source-location
        $meta = new Metadata($catalogue->getMetadata('translated.paragraph1'));
        $this->assertFalse('new' === $meta->getState());
        foreach ($meta->getSourceLocations() as $sourceLocation) {
            $this->assertNotEquals('foobar.html.twig', $sourceLocation['path']);
        }

        $meta = new Metadata($catalogue->getMetadata('not.in.source'));
        self::assertSame('obsolete', $meta->getState(), 'Expect meta state to be correct');

        $meta = new Metadata($catalogue->getMetadata('translated.title'));
        self::assertSame('new', $meta->getState(), 'Expect meta state to be correct');
    }
}
