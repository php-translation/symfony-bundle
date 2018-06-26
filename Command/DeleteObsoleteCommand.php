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
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Translation\Bundle\Catalogue\CatalogueFetcher;
use Translation\Bundle\Catalogue\CatalogueManager;
use Translation\Bundle\Service\ConfigurationManager;
use Translation\Bundle\Service\StorageManager;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class DeleteObsoleteCommand extends Command
{
    use BundleTrait, StorageTrait;

    protected static $defaultName = 'translation:delete-obsolete';

    /**
     * @var ConfigurationManager
     */
    private $configurationManager;

    /**
     * @var CatalogueManager
     */
    private $catalogueManager;

    /**
     * @var CatalogueFetcher
     */
    private $catalogueFetcher;

    /**
     * @param StorageManager       $storageManager
     * @param ConfigurationManager $configurationManager
     * @param CatalogueManager     $catalogueManager
     * @param CatalogueFetcher     $catalogueFetcher
     */
    public function __construct(
        StorageManager $storageManager,
        ConfigurationManager $configurationManager,
        CatalogueManager $catalogueManager,
        CatalogueFetcher $catalogueFetcher
    ) {
        $this->storageManager = $storageManager;
        $this->configurationManager = $configurationManager;
        $this->catalogueManager = $catalogueManager;
        $this->catalogueFetcher = $catalogueFetcher;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Delete all translations marked as obsolete.')
            ->addArgument('configuration', InputArgument::OPTIONAL, 'The configuration to use', 'default')
            ->addArgument('locale', InputArgument::OPTIONAL, 'The locale ot use. If omitted, we use all configured locales.', null)
            ->addOption('bundle', 'b', InputOption::VALUE_REQUIRED, 'The bundle you want remove translations from.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configName = $input->getArgument('configuration');
        $locales = [];
        if (null !== $inputLocale = $input->getArgument('locale')) {
            $locales = [$inputLocale];
        }

        $config = $this->configurationManager->getConfiguration($configName);
        $this->configureBundleDirs($input, $config);
        $this->catalogueManager->load($this->catalogueFetcher->getCatalogues($config, $locales));

        $storage = $this->getStorage($configName);
        $messages = $this->catalogueManager->findMessages(['locale' => $inputLocale, 'isObsolete' => true]);

        $messageCount = count($messages);
        if (0 === $messageCount) {
            $output->writeln('No messages are obsolete');

            return;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(sprintf('You are about to remove %d translations. Do you wish to continue? (y/N) ', $messageCount), false);
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $progress = null;
        if (OutputInterface::VERBOSITY_NORMAL === $output->getVerbosity() && OutputInterface::VERBOSITY_QUIET !== $output->getVerbosity()) {
            $progress = new ProgressBar($output, $messageCount);
        }
        foreach ($messages as $message) {
            $storage->delete($message->getLocale(), $message->getDomain(), $message->getKey());
            if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln(sprintf(
                    'Deleted obsolete message "<info>%s</info>" from domain "<info>%s</info>" and locale "<info>%s</info>"',
                    $message->getKey(),
                    $message->getDomain(),
                    $message->getLocale()
                ));
            }

            if ($progress) {
                $progress->advance();
            }
        }

        if ($progress) {
            $progress->finish();
        }
    }
}
