<?php

declare(strict_types=1);

namespace Translation\Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Translation\Bundle\Catalogue\CatalogueCounter;
use Translation\Bundle\Catalogue\CatalogueFetcher;
use Translation\Bundle\Model\Configuration;
use Translation\Bundle\Service\ConfigurationManager;
use Translation\Bundle\Service\Importer;

final class CheckMissingCommand extends Command
{
    protected static $defaultName = 'translation:check-missing';

    /**
     * @var ConfigurationManager
     */
    private $configurationManager;

    /**
     * @var CatalogueFetcher
     */
    private $catalogueFetcher;

    /**
     * @var Importer
     */
    private $importer;

    /**
     * @var CatalogueCounter
     */
    private $catalogueCounter;

    public function __construct(
        ConfigurationManager $configurationManager,
        CatalogueFetcher $catalogueFetcher,
        Importer $importer,
        CatalogueCounter $catalogueCounter
    ) {
        parent::__construct();

        $this->configurationManager = $configurationManager;
        $this->catalogueFetcher = $catalogueFetcher;
        $this->importer = $importer;
        $this->catalogueCounter = $catalogueCounter;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Check that all translations for a given locale are extracted.')
            ->addArgument('locale', InputArgument::REQUIRED, 'The locale to check')
            ->addArgument('configuration', InputArgument::OPTIONAL, 'The configuration to use', 'default');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->configurationManager->getConfiguration($input->getArgument('configuration'));

        $locale = $input->getArgument('locale');

        $catalogues = $this->catalogueFetcher->getCatalogues($config, [$locale]);
        $finder = $this->getConfiguredFinder($config);

        $result = $this->importer->extractToCatalogues(
            $finder,
            $catalogues,
            [
                'blacklist_domains' => $config->getBlacklistDomains(),
                'whitelist_domains' => $config->getWhitelistDomains(),
                'project_root' => $config->getProjectRoot(),
            ]
        );

        $definedBefore = $this->catalogueCounter->getNumberOfDefinedMessages($catalogues[0]);
        $definedAfter = $this->catalogueCounter->getNumberOfDefinedMessages($result->getMessageCatalogues()[0]);

        $newMessages = $definedAfter - $definedBefore;

        $io = new SymfonyStyle($input, $output);

        if ($newMessages > 0) {
            $io->error(\sprintf('%d new message(s) have been found, run bin/console translation:extract', $newMessages));

            return 1;
        }

        $emptyTranslations = $this->countEmptyTranslations($result->getMessageCatalogues()[0]);

        if ($emptyTranslations > 0) {
            $io->error(
                \sprintf('%d messages have empty translations, please provide translations for them', $emptyTranslations)
            );

            return 1;
        }

        $io->success('No new translation messages');

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

    private function countEmptyTranslations(MessageCatalogueInterface $catalogue): int
    {
        $total = 0;

        foreach ($catalogue->getDomains() as $domain) {
            $emptyTranslations = \array_filter(
                $catalogue->all($domain),
                function (string $message = null): bool {
                    return null === $message || '' === $message;
                }
            );

            $total += \count($emptyTranslations);
        }

        return $total;
    }
}
