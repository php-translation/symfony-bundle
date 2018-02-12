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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Translation\Bundle\Service\StorageManager;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class SyncCommand extends Command
{
    protected static $defaultName = 'translation:sync';

    /**
     * @var StorageManager
     */
    private $storageManager;

    /**
     * @param StorageManager $storageManager
     */
    public function __construct(StorageManager $storageManager)
    {
        $this->storageManager = $storageManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->getName(self::$defaultName)
            ->setDescription('Sync the translations with the remote storage')
            ->addArgument('configuration', InputArgument::OPTIONAL, 'The configuration to use', 'default');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configName = $input->getArgument('configuration');
        $this->storageManager->getStorage($configName)->sync();
    }
}
