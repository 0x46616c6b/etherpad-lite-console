<?php

namespace EtherpadLite\Console\Command;

use EtherpadLite\Client;
use EtherpadLite\Helper\Pad;
use EtherpadLite\Response;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PurgePadCommand extends Command
{
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $threshold = null;
    protected $countPads = 0;
    protected $countPadsFailed = 0;
    protected $countPadsDeleted = 0;

    protected function configure()
    {
        $this->setName('pad:purge')
            ->setDescription('Purge pads which older then x days')
            ->setDefinition(
                array(
                    new InputOption('apikey', null, InputOption::VALUE_REQUIRED, 'The API Key of your Etherpad Instance'),
                    new InputOption('days', null, InputOption::VALUE_OPTIONAL, 'Days after Pads will deleted', '30'),
                    new InputOption('host', null, InputOption::VALUE_OPTIONAL, 'The HTTP Address of your Etherpad Instance', 'http://localhost:9001'),
                    new InputOption('dry-run', 'd', InputOption::VALUE_NONE, '')
                )
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setThreshold($input->getOption('days'));

        if ($input->getOption('dry-run')) {
            $output->writeln('<info>This is a dry-run run, no pad will deleted.</info>');
        }

        if ($output->isVerbose()) {
            $output->writeln(
                sprintf('<info>INFO:</info> Pads before %s will be deleted', $this->threshold->format($this->dateFormat))
            );
        }

        $padIds = $this->getAllPads($input->getOption('apikey'), $input->getOption('host'));

        if ($padIds === false) {
            $output->writeln('<error>Could not receive all pads.</error>');
            return;
        }

        if ($output->isVerbose()) {
            $output->writeln(
                sprintf('<info>INFO:</info> %s pad(s) stored', $this->countPads)
            );
        }

        foreach ($padIds as $padId) {
            $lastEdited = Pad::getLastEdited(
                $padId,
                $input->getOption('apikey'),
                $input->getOption('host')
            );

            if ($lastEdited === false) {
                $this->countPadsFailed++;
                continue;
            }

            if ($lastEdited < $this->threshold->getTimestamp()) {
                if ($output->isDebug()){
                    $output->writeln(
                        sprintf(
                            '<info>DEBUG:</info> "%s" was last edited on %s and will purged',
                            $padId,
                            date($this->dateFormat, $lastEdited)
                        )
                    );
                }

                if (!$input->getOption('dry-run')) {
                    if (!Pad::deletePad(
                        $padId,
                        $input->getOption('apikey'),
                        $input->getOption('host')
                    )) {
                        $this->countPadsFailed++;
                    }
                }

                $this->countPadsDeleted++;
            }
        }

        if ($output->isVerbose()) {
            $output->writeln(sprintf('<info>INFO:</info> %s pad(s) deleted', $this->countPadsDeleted));
            $output->writeln(sprintf('<info>INFO:</info> %s pad(s) failed', $this->countPadsFailed));
        }
    }

    /**
     * @param $days
     */
    private function setThreshold($days)
    {
        $this->threshold = new \DateTime();
        $this->threshold->sub(new \DateInterval(
                sprintf('P%sD', $days)
            )
        );
    }

    /**
     * @param $apikey
     * @param $host
     * @return array
     */
    private function getAllPads($apikey, $host)
    {
        $pads = Pad::getAllPadIds($apikey, $host);

        $this->countPads = count($pads);

        return $pads;
    }
}