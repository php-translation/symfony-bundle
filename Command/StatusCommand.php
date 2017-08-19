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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class StatusCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('translation:status')
            ->setDescription('Show status about your translations.')
            ->addArgument('configuration', InputArgument::OPTIONAL, 'The configuration to use', 'default')
            ->addArgument('locale', InputArgument::OPTIONAL, 'The locale ot use. If omitted, we use all configured locales.', false)
            ->addOption('json', null, InputOption::VALUE_NONE, 'If we should output in Json format');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $counter = $container->get('php_translation.catalogue_counter');
        $config = $container->get('php_translation.configuration_manager')
            ->getConfiguration($input->getArgument('configuration'));

        $locales = [];
        if ($inputLocale = $input->getArgument('locale')) {
            $locales = [$inputLocale];
        }

        $catalogues = $container->get('php_translation.catalogue_fetcher')
            ->getCatalogues($config, $locales);

        $stats = [];
        foreach ($catalogues as $catalogue) {
            $stats[$catalogue->getLocale()] = $counter->getCatalogueStatistics($catalogue);
        }

        if ($input->getOption('json')) {
            $output->writeln(json_encode($stats));

            return;
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
    }
}
