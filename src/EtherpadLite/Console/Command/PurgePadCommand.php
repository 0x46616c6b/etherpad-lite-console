<?php

namespace EtherpadLite\Console\Command;

use DateInterval;
use DateTime;
use EtherpadLite\Helper\Pad;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PurgePadCommand extends Command
{
    /** @var string */
    protected $dateFormat = 'Y-m-d H:i:s';
    /** @var null|DateTime */
    protected $threshold = null;
    /** @var int */
    protected $countPads = 0;
    /** @var int */
    protected $countPadsFailed = 0;
    /** @var int */
    protected $countPadsDeleted = 0;

    protected function configure()
    {
        $this->setName('pad:purge')
            ->setDescription('Purge pads older than x days')
            ->setDefinition(
                array(
                    new InputOption('apikey', null, InputOption::VALUE_REQUIRED, 'The API Key of your Etherpad Instance'),
                    new InputOption('days', null, InputOption::VALUE_OPTIONAL, 'Days after Pads will deleted', '30'),
                    new InputOption('suffix', null, InputOption::VALUE_OPTIONAL, 'Only delete pads with this suffix', null),
                    new InputOption('ignore-suffix', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Ignore pads with this suffix', []),
                    new InputOption('host', null, InputOption::VALUE_OPTIONAL, 'The HTTP Address of your Etherpad Instance', 'http://localhost:9001'),
                    new InputOption('dry-run', 'd', InputOption::VALUE_NONE, ''),
                )
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setThreshold($input->getOption('days'));

        $suffix = $input->getOption('suffix');
        $ignoreSuffixes = $input->getOption('ignore-suffix');

        $suffixRegex = sprintf('/%s$/', $suffix);
        $ignoreSuffixesRegex = sprintf('/(%s)$/', join('|', $ignoreSuffixes));

        if ($input->getOption('dry-run')) {
            $output->writeln('<info>This is a dry-run run, no pad will deleted.</info>');
        }

        if ($output->isVerbose()) {
            $output->writeln(
                sprintf('<info>INFO:</info> Pads before %s will be deleted', $this->threshold->format($this->dateFormat))
            );
            if (null !== $suffix) {
                $output->writeln(
                    sprintf('<info>INFO:</info> Only pads ending with suffix \'%s\' will be deleted', $suffix)
                );
            }
            if (!empty($ignoreSuffixes)) {
                $output->writeln(
                    sprintf('<info>INFO:</info> Pads ending with suffixes \'%s\' will be ignored', join('\', \'', $ignoreSuffixes))
                );
            }
        }

        $padIds = $this->getAllPads($input->getOption('apikey'), $input->getOption('host'));

        if ($padIds === false) {
            $output->writeln('<error>Could not receive all pads.</error>');

            return 0;
        }

        if ($output->isVerbose()) {
            $output->writeln(
                sprintf('<info>INFO:</info> %s pad(s) stored', $this->countPads)
            );
        }

        foreach ($padIds as $padId) {
            if (null !== $suffix && !preg_match($suffixRegex, $padId)) {
                if ($output->isDebug()) {
                    $output->writeln(
                        sprintf('<info>DEBUG:</info> "%s" will be ignored as it doesn\'t match suffix', $padId)
                    );
                }
                continue;
            }
            if (!empty($ignoreSuffixes) && preg_match($ignoreSuffixesRegex, $padId)) {
                if ($output->isDebug()) {
                    $output->writeln(
                        sprintf('<info>DEBUG:</info> "%s" will be ignored as it matches an ignore-suffix', $padId)
                    );
                }
                continue;
            }

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
                if ($output->isDebug()) {
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

        return 0;
    }

    /**
     * @param $days
     * @throws Exception
     */
    private function setThreshold($days)
    {
        $this->threshold = new DateTime();
        $this->threshold->sub(
            new DateInterval(
                sprintf('P%sD', $days)
            )
        );
    }

    /**
     * @param $apikey
     * @param $host
     * @return array
     * @throws Exception
     */
    private function getAllPads($apikey, $host)
    {
        $pads = Pad::getAllPadIds($apikey, $host);

        $this->countPads = count($pads);

        return $pads;
    }
}
