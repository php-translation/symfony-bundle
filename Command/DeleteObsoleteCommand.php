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
use Translation\Bundle\Catalogue\CatalogueManager;
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
            ->addArgument('configuration', InputArgument::OPTIONAL, 'The configuration to use', 'default')
            ->addArgument('locale', InputArgument::OPTIONAL, 'The locale ot use. If omitted, we use all configured locales.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $configName = $input->getArgument('configuration');
        $locales = [];
        if (null !== $inputLocale = $input->getArgument('locale', null)) {
            $locales = [$inputLocale];
        }

        $catalogueManager = $container->get('php_translation.catalogue_manager');
        $config = $container->get('php_translation.configuration_manager')->getConfiguration($configName);
        $catalogueManager->load($container->get('php_translation.catalogue_fetcher')->getCatalogues($config, $locales));

        $storage = $container->get('php_translation.storage.'.$configName);
        $messages = $catalogueManager->findMessages(['locale' => $inputLocale, 'isObsolete' => true]);

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
}
