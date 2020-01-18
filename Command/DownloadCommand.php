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
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Translation\Bundle\Catalogue\CatalogueWriter;
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

    private $configurationManager;
    private $cacheCleaner;
    private $catalogueWriter;

    public function __construct(
        StorageManager $storageManager,
        ConfigurationManager $configurationManager,
        CacheClearer $cacheCleaner,
        CatalogueWriter $catalogueWriter
    ) {
        $this->storageManager = $storageManager;
        $this->configurationManager = $configurationManager;
        $this->cacheCleaner = $cacheCleaner;
        $this->catalogueWriter = $catalogueWriter;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Replace local messages with messages from remote')
            ->setHelp(<<<EOT
The <info>%command.name%</info> will erase all your local translations and replace them with translations downloaded from the remote.
EOT
            )
            ->addArgument('configuration', InputArgument::OPTIONAL, 'The configuration to use', 'default')
            ->addOption('cache', null, InputOption::VALUE_NONE, '[DEPRECATED] Cache is now automatically cleared when translations have changed.')
            ->addOption('bundle', 'b', InputOption::VALUE_REQUIRED, 'The bundle you want update translations from.')
            ->addOption('export-config', 'exconf', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Options to send to the StorageInterface::export() function. Ie, when downloading. Example: --export-config foo:bar', [])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('cache')) {
            $message = 'The --cache option is deprecated as it\'s now the default behaviour of this command.';

            $io->note($message);
            @\trigger_error($message, E_USER_DEPRECATED);
        }

        $configName = $input->getArgument('configuration');
        $config = $this->configurationManager->getConfiguration($configName);
        $storage = $this->getStorage($configName);

        $this->configureBundleDirs($input, $config);

        $translationsDirectory = $config->getOutputDir();
        $md5BeforeDownload = $this->hashDirectory($translationsDirectory);

        $exportOptions = $this->cleanParameters($input->getOption('export-config'));
        $catalogues = $storage->download($exportOptions);
        $this->catalogueWriter->writeCatalogues($config, $catalogues);

        $translationsCount = 0;
        foreach ($catalogues as $locale => $catalogue) {
            foreach ($catalogue->all() as $domain => $messages) {
                $translationsCount += \count($messages);
            }
        }

        $io->text("<info>$translationsCount</info> translations have been downloaded.");

        $md5AfterDownload = $this->hashDirectory($translationsDirectory);

        if ($md5BeforeDownload !== $md5AfterDownload) {
            $io->success('Translations updated successfully!');
            $this->cacheCleaner->clearAndWarmUp();
        } else {
            $io->success('All translations were up to date.');
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
