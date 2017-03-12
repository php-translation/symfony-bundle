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
use Symfony\Component\Finder\Finder;
use Translation\Bundle\Model\Configuration;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ExtractCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('translation:extract')
            ->setDescription('Extract translations from source code.')
            ->addArgument('configuration', InputArgument::OPTIONAL, 'The configuration to use', 'default')
            ->addArgument('locale', InputArgument::OPTIONAL, 'The locale ot use. If omitted, we use all configured locales.', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $importer = $container->get('php_translation.importer');
        $config = $container->get('php_translation.configuration_manager')
            ->getConfiguration($input->getArgument('configuration'));

        $locales = [];
        if ($inputLocale = $input->getArgument('locale')) {
            $locales = [$inputLocale];
        }

        $catalogues = $container->get('php_translation.catalogue_fetcher')
            ->getCatalogues($config, $locales);

        $finder = $this->getConfiguredFinder($config);
        $results = $importer->extractToCatalogues($finder, $catalogues, [
            'blacklist_domains' => $config->getBlacklistDomains(),
            'whitelist_domains' => $config->getWhitelistDomains(),
            'project_root' => $config->getProjectRoot(),
        ]);

        $container->get('php_translation.catalogue_writer')
            ->writeCatalogues($config, $results);
    }

    /**
     * @param Configuration $config
     *
     * @return Finder
     */
    private function getConfiguredFinder(Configuration $config)
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
