<?php

namespace EtherpadLite\Console\Command;

use EtherpadLite\Client;
use EtherpadLite\Helper\Pad;
use EtherpadLite\Response;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteOldPadsCommand extends Command
{
    protected function configure()
    {
        $this->setName('delete-old-pads')
            ->setDescription('Deletes Pads which older then x days')
            ->setDefinition(
                array(
                    new InputOption('apikey', null, InputOption::VALUE_REQUIRED, 'The API Key of your Etherpad Instance'),
                    new InputOption('days', null, InputOption::VALUE_OPTIONAL, 'Days after Pads will deleted', 30),
                    new InputOption('host', null, InputOption::VALUE_OPTIONAL, 'The HTTP Address of your Etherpad Instance', 'http://localhost:9001')
                )
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $threshold = new \DateTime();
        $threshold->sub(new \DateInterval(
                sprintf('P%sD', $input->getOption('days'))
            )
        );

        $padIds = Pad::getAllPadIds(
            $input->getOption('apikey'),
            $input->getOption('host')
        );

        $deletedPads = 0;

        foreach ($padIds as $padId) {
            $lastEdited = Pad::getLastEdited(
                $padId,
                $input->getOption('apikey'),
                $input->getOption('host')
            );

            if ($lastEdited < $threshold->getTimestamp()) {
                Pad::deletePad(
                    $padId,
                    $input->getOption('apikey'),
                    $input->getOption('host')
                );

                $deletedPads =+ 1;
            }
        }

        $output->writeln('Pads (Count): ' . count($padIds));
        $output->writeln('Pads deleted: ' . $deletedPads);
    }
}