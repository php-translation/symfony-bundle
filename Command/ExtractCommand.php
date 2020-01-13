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
use Translation\Bundle\Catalogue\CatalogueCounter;
use Translation\Bundle\Catalogue\CatalogueFetcher;
use Translation\Bundle\Catalogue\CatalogueWriter;
use Translation\Bundle\Model\Configuration;
use Translation\Bundle\Service\ConfigurationManager;
use Translation\Bundle\Service\Importer;
use Translation\Extractor\Model\Error;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ExtractCommand extends Command
{
    use BundleTrait;

    protected static $defaultName = 'translation:extract';

    /**
     * @var CatalogueFetcher
     */
    private $catalogueFetcher;

    /**
     * @var CatalogueWriter
     */
    private $catalogueWriter;

    /**
     * @var CatalogueCounter
     */
    private $catalogueCounter;

    /**
     * @var Importer
     */
    private $importer;

    /**
     * @var ConfigurationManager
     */
    private $configurationManager;

    public function __construct(
        CatalogueFetcher $catalogueFetcher,
        CatalogueWriter $catalogueWriter,
        CatalogueCounter $catalogueCounter,
        Importer $importer,
        ConfigurationManager $configurationManager
    ) {
        $this->catalogueFetcher = $catalogueFetcher;
        $this->catalogueWriter = $catalogueWriter;
        $this->catalogueCounter = $catalogueCounter;
        $this->importer = $importer;
        $this->configurationManager = $configurationManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Extract translations from source code.')
            ->addArgument('configuration', InputArgument::OPTIONAL, 'The configuration to use', 'default')
            ->addArgument('locale', InputArgument::OPTIONAL, 'The locale to use. If omitted, we use all configured locales.', false)
            ->addOption('hide-errors', null, InputOption::VALUE_NONE, 'If we should print error or not')
            ->addOption('bundle', 'b', InputOption::VALUE_REQUIRED, 'The bundle you want extract translations from.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configName = $input->getArgument('configuration');
        $config = $this->configurationManager->getConfiguration($configName);

        $locales = [];
        if ($inputLocale = $input->getArgument('locale')) {
            $locales = [$inputLocale];
        }

        $this->configureBundleDirs($input, $config);
        $catalogues = $this->catalogueFetcher->getCatalogues($config, $locales);
        $finder = $this->getConfiguredFinder($config);

        $result = $this->importer->extractToCatalogues($finder, $catalogues, [
            'blacklist_domains' => $config->getBlacklistDomains(),
            'whitelist_domains' => $config->getWhitelistDomains(),
            'project_root' => $config->getProjectRoot(),
        ]);
        $errors = $result->getErrors();

        $this->catalogueWriter->writeCatalogues($config, $result->getMessageCatalogues());

        $definedBefore = $this->catalogueCounter->getNumberOfDefinedMessages($catalogues[0]);
        $definedAfter = $this->catalogueCounter->getNumberOfDefinedMessages($result->getMessageCatalogues()[0]);

        /*
         * Print results
         */
        $io = new SymfonyStyle($input, $output);
        $io->table(['Type', 'Count'], [
            ['Total defined messages', $definedAfter],
            ['New messages', $definedAfter - $definedBefore],
            ['Errors', \count($errors)],
        ]);

        if (!$input->getOption('hide-errors')) {
            /** @var Error $error */
            foreach ($errors as $error) {
                $io->error(
                    \sprintf("%s\nLine: %s\nMessage: %s", $error->getPath(), $error->getLine(), $error->getMessage())
                );
            }
        }

        return 0;
    }

    private function getConfiguredFinder(Configuration $config): Finder
    {
        $finder = new Finder();
        $finder->in($config->getDirs());

        foreach ($config->getExcludedDirs() as $exclude) {
            $finder->notPath($exclude);
        }

        foreach ($config->getExcludedNames() as $exclude) {
            $finder->notName($exclude);
        }

        return $finder;
    }
}
