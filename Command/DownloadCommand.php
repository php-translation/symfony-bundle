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
use Symfony\Component\Finder\Finder;
use Translation\Bundle\Service\CacheClearer;
use Translation\Bundle\Service\ConfigurationManager;
use Translation\Bundle\Service\StorageManager;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class DownloadCommand extends Command
{
    use BundleTrait;
    use StorageTrait;

    protected static $defaultName = 'translation:download';

    /**
     * @var ConfigurationManager
     */
    private $configurationManager;

    /**
     * @var CacheClearer
     */
    private $cacheCleaner;

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

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Replace local messages with messages from remote')
            ->addArgument('configuration', InputArgument::OPTIONAL, 'The configuration to use', 'default')
            ->addOption('cache', null, InputOption::VALUE_NONE, 'Clear the cache if the translations have changed')
            ->addOption('bundle', 'b', InputOption::VALUE_REQUIRED, 'The bundle you want update translations from.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configName = $input->getArgument('configuration');
        $storage = $this->getStorage($configName);

        if (null === $configuration = $this->configurationManager->getConfiguration($configName)) {
            throw new \InvalidArgumentException(\sprintf('No configuration found for "%s"', $configName));
        }

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

        return 0;
    }

    /**
     * @return bool|string
     */
    private function hashDirectory(string $directory)
    {
        if (!\is_dir($directory)) {
            return false;
        }

        $finder = new Finder();
        $finder->files()->in($directory)->notName('/~$/')->sortByName();

        $hash = \hash_init('md5');
        foreach ($finder as $file) {
            \hash_update_file($hash, $file->getRealPath());
        }

        return \hash_final($hash);
    }
}
