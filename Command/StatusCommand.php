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
use Translation\Bundle\Catalogue\CatalogueCounter;
use Translation\Bundle\Catalogue\CatalogueFetcher;
use Translation\Bundle\Service\ConfigurationManager;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class StatusCommand extends Command
{
    use BundleTrait;

    protected static $defaultName = 'translation:status';

    /**
     * @var CatalogueCounter
     */
    private $catalogueCounter;

    /**
     * @var ConfigurationManager
     */
    private $configurationManager;

    /**
     * @var CatalogueFetcher
     */
    private $catalogueFetcher;

    public function __construct(
        CatalogueCounter $catalogueCounter,
        ConfigurationManager $configurationManager,
        CatalogueFetcher $catalogueFetcher
    ) {
        $this->catalogueCounter = $catalogueCounter;
        $this->configurationManager = $configurationManager;
        $this->catalogueFetcher = $catalogueFetcher;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Show status about your translations.')
            ->addArgument('configuration', InputArgument::OPTIONAL, 'The configuration to use', 'default')
            ->addArgument('locale', InputArgument::OPTIONAL, 'The locale to use. If omitted, we use all configured locales.', false)
            ->addOption('json', null, InputOption::VALUE_NONE, 'If we should output in Json format')
            ->addOption('bundle', 'b', InputOption::VALUE_REQUIRED, 'The bundle for which you want to check the translations.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configName = $input->getArgument('configuration');
        $config = $this->configurationManager->getConfiguration($configName);

        $this->configureBundleDirs($input, $config);

        $locales = [];
        if ($inputLocale = $input->getArgument('locale')) {
            $locales = [$inputLocale];
        }

        $catalogues = $this->catalogueFetcher->getCatalogues($config, $locales);

        $stats = [];
        foreach ($catalogues as $catalogue) {
            $stats[$catalogue->getLocale()] = $this->catalogueCounter->getCatalogueStatistics($catalogue);
        }

        if ($input->getOption('json')) {
            $output->writeln(\json_encode($stats));

            return 0;
        }

        $io = new SymfonyStyle($input, $output);
        foreach ($stats as $locale => $stat) {
            $rows = [];
            foreach ($stat as $domain => $data) {
                $rows[] = [$domain, $data['defined'], $data['new'], $data['obsolete']];
            }
            $io->title('Locale: '.$locale);
            $io->table(['Domain', 'Defined', 'New', 'Obsolete'], $rows);
        }

        return 0;
    }
}
