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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Translation\Bundle\Model\Configuration;

class ExtractCommand extends ContainerAwareCommand
{
    /**
     * @var ContainerInterface
     */
    private $container;

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
        $config = $this->getContainer()->get('php_translation.configuration_manager')->getConfiguration($input->getArgument('configuration'));
        $importer = $this->getContainer()->get('php_translation.importer');

        if ($inputLocale = $input->getArgument('locale')) {
            $locales = [$inputLocale];
        } else {
            $locales = $this->getContainer()->getParameter('php_translation.locales');
        }

        $transPaths = $config->getPathsToTranslationFiles();
        $catalogues = $this->getContainer()->get('php_translation.catalogue_fetcher')->getCatalogues($locales, $transPaths);
        $finder = $this->getConfiguredFinder($config);
        $results = $importer->extractToCatalogues($finder, $catalogues, [
            'blacklist_domains'=>$config->getBlacklistDomains(),
            'whitelist_domains'=>$config->getWhitelistDomains(),
            'project_root'=>$config->getProjectRoot(),
        ]);

        $writer = $this->getContainer()->get('translation.writer');
        foreach ($results as $result) {
            $writer->writeTranslations(
                $result,
                $config->getOutputFormat(),
                [
                    'path' => $config->getOutputDir(),
                    'default_locale' => $this->getContainer()->getParameter('php_translation.default_locale'),
                ]
            );
        }
    }

    /**
     * @param Configuration $configuration
     *
     * @return Finder
     */
    private function getConfiguredFinder(Configuration $config)
    {
        // 'dirs', 'excluded_dirs', 'excluded_names'

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
