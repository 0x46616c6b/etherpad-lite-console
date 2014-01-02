<?php

namespace EtherpadLite\Console\Command;

use EtherpadLite\Client;
use EtherpadLite\Helper\Pad;
use EtherpadLite\Response;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeletePadCommand extends Command
{
    protected function configure()
    {
        $this->setName('pad:delete')
            ->setDescription('Delete a pad')
            ->setDefinition(
                array(
                    new InputArgument('padId', InputArgument::REQUIRED, 'The ID of the Pad'),
                    new InputOption('apikey', null, InputOption::VALUE_REQUIRED, 'The API Key of your Etherpad Instance'),
                    new InputOption('host', null, InputOption::VALUE_OPTIONAL, 'The HTTP Address of your Etherpad Instance', 'http://localhost:9001')
                )
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (Pad::deletePad(
            $input->getArgument('padId'),
            $input->getOption('apikey'),
            $input->getOption('host')
        )) {
            $output->writeln('Pad sucessfully deleted!');
        } else {
            $output->writeln('Pad could not deleted!');
        }
    }
}