<?php

namespace Translation\Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExtractCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('translation:extract')
            ->setDescription('Extract translations from source code.')
            ->addArgument('configuration', InputArgument::REQUIRED, 'The configuration to use');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
