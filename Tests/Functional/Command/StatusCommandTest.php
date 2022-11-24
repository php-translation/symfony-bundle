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
use Translation\Bundle\Command\StatusCommand;
use Translation\Bundle\Tests\Functional\BaseTestCase;

class StatusCommandTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->testKernel->addTestConfig(__DIR__.'/../app/config/normal_config.yaml');
    }

    public function testExecute(): void
    {
        $this->testKernel->boot();
        $application = new Application($this->testKernel);

        $container = $this->testKernel->getContainer();
        $application->add($container->get(StatusCommand::class));

        $command = $application->find('translation:status');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'configuration' => 'app',
            'locale' => 'en',
            '--json' => true,
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $data = json_decode($output, true);

        $this->assertArrayHasKey('en', $data);
        $this->assertArrayHasKey('messages', $data['en']);
        $this->assertArrayHasKey('_total', $data['en']);

        $total = $data['en']['_total'];
        $this->assertArrayHasKey('defined', $total);
        $this->assertArrayHasKey('new', $total);
        $this->assertArrayHasKey('obsolete', $total);
        $this->assertArrayHasKey('approved', $total);
        $this->assertEquals(4, $total['defined']);
        $this->assertEquals(2, $total['new']);
        $this->assertEquals(0, $total['obsolete']);
        $this->assertEquals(2, $total['approved']);
    }
}
