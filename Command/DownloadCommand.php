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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Translation\Bundle\Service\StorageService;
use Translation\Bundle\Model\Configuration;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class DownloadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('translation:download')
            ->setDescription('Replace local messages with messages from remote')
            ->addArgument('configuration', InputArgument::OPTIONAL, 'The configuration to use', 'default')
            ->addOption('cache', null, InputOption::VALUE_NONE, 'Clear the cache if the translations have changed')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $configName = $input->getArgument('configuration');

        /** @var StorageService $storage */
        $storage = $container->get('php_translation.storage.'.$configName);
        /** @var Configuration $configuration */
        $configuration = $this->getContainer()->get('php_translation.configuration.'.$configName);

        if ($input->getOption('cache')) {
            $translationsDirectory = $configuration->getOutputDir();
            $md5BeforeDownload = $this->hashDirectory($translationsDirectory);
            $storage->download();
            $md5AfterDownload = $this->hashDirectory($translationsDirectory);

            if ($md5BeforeDownload !== $md5AfterDownload) {
                $command = $this->getApplication()->find('cache:clear');
                $command->run(new ArrayInput(['--no-warmup' => true]), $output);
            }
        } else {
            $storage->download();
        }
    }

    private function hashDirectory($directory)
    {
        if (!is_dir($directory)) {
            return false;
        }

        $files = [];
        $dir = dir($directory);

        while (false !== ($file = $dir->read())) {
            if ($file !== '.' and $file !== '..') {
                if (is_dir($directory.'/'.$file)) {
                    $files[] = $this->hashDirectory($directory.'/'.$file);
                } else {
                    $files[] = md5_file($directory.'/'.$file);
                }
            }
        }

        $dir->close();

        return md5(implode('', $files));
    }
}
