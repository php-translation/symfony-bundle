<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Translation\Bundle\Service\StorageService;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class SyncCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('translation:sync')
            ->setDescription('Sync the translations with the remote storage')
            ->addArgument('configuration', InputArgument::OPTIONAL, 'The configuration to use', 'default');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $configName = $input->getArgument('configuration');
        /** @var StorageService $storage */
        $storage = $container->get('php_translation.storage.'.$configName);
        $storage->sync();
    }
}
