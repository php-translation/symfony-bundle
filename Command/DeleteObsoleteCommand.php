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
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Translation\Bundle\Service\StorageService;

class DeleteObsoleteCommand extends ContainerAwareCommand
{
    /**
     * @var ContainerInterface
     */
    private $container;

    protected function configure()
    {
        $this
            ->setName('translation:delete-obsolete')
            ->setDescription('Delete all translations marked as obsolete.')
            ->addArgument('configuration', InputArgument::REQUIRED, 'The configuration to use')
            ->addArgument('locale', InputArgument::OPTIONAL, 'The locale ot use. If omitted, we use all configured locales.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configName = $input->getArgument('configuration');
        $config = $this->getContainer()->get('php_translation.configuration_manager')->getConfiguration($configName);
        if (null !== $inputLocale = $input->getArgument('locale', null)) {
            $locales = [$inputLocale];
        } else {
            $locales = $this->getContainer()->getParameter('php_translation.locales');
        }

        $transPaths = array_merge($config['external_translations_dirs'], [$config['output_dir']]);
        $catalogueManager = $this->getContainer()->get('php_translation.catalogue_manager');
        $catalogueManager->load($this->getContainer()->get('php_translation.catalogue_fetcher')->getCatalogues($locales, $transPaths));

        /** @var StorageService $storage */
        $storage = $this->getContainer()->get('php_translation.storage.'.$configName);
        $messages = $catalogueManager->findMessages(['locale'=>$inputLocale, 'isObsolete'=>true]);

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(sprintf('You are about to remove %d translations. Do you wish to continue?', count($messages)), false);
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $progress = new ProgressBar($output, count($messages));
        foreach ($messages as $message) {
            $storage->delete($message->getLocale(), $message->getDomain(), $message->getKey());
            $progress->advance();
        }
        $progress->finish();

    }

    /**
     * @param array $configuration
     *
     * @return Finder
     */
    private function getConfiguredFinder(array $config)
    {
        // 'dirs', 'excluded_dirs', 'excluded_names'

        $finder = new Finder();
        $finder->in($config['dirs']);

        foreach ($config['excluded_dirs'] as $exclude) {
            $finder->notPath($exclude);
        }

        foreach ($config['excluded_names'] as $exclude) {
            $finder->notName($exclude);
        }

        return $finder;
    }
}
