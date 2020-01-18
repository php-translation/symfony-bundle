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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Translation\Bundle\Service\StorageManager;
use Translation\Bundle\Service\StorageService;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class SyncCommand extends Command
{
    use StorageTrait;

    protected static $defaultName = 'translation:sync';

    public function __construct(StorageManager $storageManager)
    {
        $this->storageManager = $storageManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Sync the translations with the remote storage')
            ->addArgument('configuration', InputArgument::OPTIONAL, 'The configuration to use', 'default')
            ->addArgument('direction', InputArgument::OPTIONAL, 'Use "down" if local changes should be overwritten, otherwise "up"', 'down')
            ->addOption('export-config', 'exconf', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Options to send to the StorageInterface::export() function. Ie, when downloading. Example: --export-config foo:bar', [])
            ->addOption('import-config', 'imconf', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Options to send to the StorageInterface::import() function. Ie, when uploading. Example: --import-config foo:bar', [])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        switch ($input->getArgument('direction')) {
            case 'down':
                $direction = StorageService::DIRECTION_DOWN;

                break;
            case 'up':
                $direction = StorageService::DIRECTION_UP;

                break;
            default:
                $output->writeln(\sprintf('Direction must be either "up" or "down". Not "%s".', $input->getArgument('direction')));

                return 0;
        }

        $export = $this->cleanParameters($input->getOption('export-config'));
        $import = $this->cleanParameters($input->getOption('import-config'));

        $this->getStorage($input->getArgument('configuration'))->sync($direction, $import, $export);

        return 0;
    }

    public function cleanParameters(array $raw)
    {
        $config = [];

        foreach ($raw as $string) {
            // Assert $string looks like "foo:bar"
            list($key, $value) = \explode(':', $string, 2);
            $config[$key][] = $value;
        }

        return $config;
    }
}
