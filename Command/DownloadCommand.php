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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Translation\Bundle\Service\CacheClearer;
use Translation\Bundle\Service\ConfigurationManager;
use Translation\Bundle\Service\StorageManager;
use Translation\Bundle\Model\Configuration;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class DownloadCommand extends Command
{
    use BundleTrait, StorageTrait;

    protected static $defaultName = 'translation:download';

    /**
     * @var ConfigurationManager
     */
    private $configurationManager;

    /**
     * @var CacheClearer
     */
    private $cacheCleaner;

    /**
     * @param StorageManager       $storageManager
     * @param ConfigurationManager $configurationManager
     * @param CacheClearer         $cacheCleaner
     */
    public function __construct(
        StorageManager $storageManager,
        ConfigurationManager $configurationManager,
        CacheClearer $cacheCleaner
    ) {
        $this->storageManager = $storageManager;
        $this->configurationManager = $configurationManager;
        $this->cacheCleaner = $cacheCleaner;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Replace local messages with messages from remote')
            ->addArgument('configuration', InputArgument::OPTIONAL, 'The configuration to use', 'default')
            ->addOption('cache', null, InputOption::VALUE_NONE, 'Clear the cache if the translations have changed')
            ->addOption('bundle', 'b', InputOption::VALUE_REQUIRED, 'The bundle you want update translations from.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configName = $input->getArgument('configuration');
        $storage = $this->getStorage($configName);
        $configuration = $this->configurationManager->getConfiguration($configName);

        $this->configureBundleDirs($input, $configuration);

        if ($input->getOption('cache')) {
            $translationsDirectory = $configuration->getOutputDir();
            $md5BeforeDownload = $this->hashDirectory($translationsDirectory);
            $storage->download();
            $md5AfterDownload = $this->hashDirectory($translationsDirectory);

            if ($md5BeforeDownload !== $md5AfterDownload) {
                $this->cacheCleaner->clearAndWarmUp();
            }
        } else {
            $storage->download();
        }
    }

    private function hashDirectory($directory)
    {
        if (!is_dir($directory)) {
            return false;
        }

        $finder = new Finder();
        $finder->files()->in($directory)->notName('/~$/')->sortByName();

        $hash = hash_init('md5');
        foreach ($finder as $file) {
            hash_update_file($hash, $file->getRealPath());
        }

        return hash_final($hash);
    }
}
