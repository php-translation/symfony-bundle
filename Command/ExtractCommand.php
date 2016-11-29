<?php

namespace Translation\Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;

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
            ->addArgument('configuration', InputArgument::REQUIRED, 'The configuration to use');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getContainer()->get('php_translation.configuration_manager')->getConfiguration($input->getArgument('configuration'));
        $importer = $this->getContainer()->get('php_translation.importer');

        // TODO let the be configurable with arguments
        $locales= $this->getContainer()->getParameter('php_translation.locales');

        $finder = new Finder();
        // TODO configure Finder with $config

        // TODO get paths from config
        $catalogues = $this->getCatalogues($locales, ['']);
        $results = $importer->extractToCatalogues($finder, $catalogues, $config);

        $writer = $this->getContainer()->get('translation.writer');
        foreach ($results as $result) {
            $writer->writeTranslations(
                $result,
                $input->getOption('output-format'),
                array(
                    'path' => $bundleTransPath,
                    'default_locale' => $this->getContainer()->getParameter('kernel.default_locale')
                )
            );
        }
    }

    /**
     * load any existing messages from the translation files
     *
     * @param array $locales
     * @param array $transPaths
     *
     * @return MessageCatalogue[]
     */
    public function getCatalogues(array $locales, array $transPaths)
    {
        $catalogues = [];
        $loader = $this->getContainer()->get('translation.loader');

        foreach ($locales as $locale) {
            $currentCatalogue = new MessageCatalogue($locale);
            foreach ($transPaths as $path) {
                if (is_dir($path)) {
                    $loader->loadMessages($path, $currentCatalogue);
                }
            }
            $catalogues[] = $currentCatalogue;
        }

        return $catalogues;
    }

}
