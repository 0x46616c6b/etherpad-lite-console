<?php

namespace EtherpadLite\Console\Command;

use EtherpadLite\Client;
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
        $this->setName('delete-pad')
            ->setDescription('Deletes a Pad by PadId')
            ->setDefinition(
                array(
                    new InputArgument('padId', InputArgument::REQUIRED, 'The ID of the Pad'),
                    new InputOption('apikey', null, InputOption::VALUE_REQUIRED, 'The API Key of your Etherpad Instance'),
                    new InputOption('host', null, InputOption::VALUE_OPTIONAL, '(optional) The HTTP Address of your Etherpad Instance', 'http://localhost:9001')
                )
            )
            ->setHelp(<<<EOT
The <info>delete-pad</info> command deletes a pad by the given padId
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $padId = $input->getArgument('padId');
        $apiKey = $input->getOption('apikey');
        $host = $input->getOption('host');

        $client = new Client($apiKey, $host);

        $response = $client->deletePad($padId);

        if ($response->getCode() == Response::CODE_OK) {
            $output->writeln('The pad was deleted successfully!');
        } else {
            $output->writeln('An error occurred!');
            $output->writeln($response->getMessage());
        }

    }
}