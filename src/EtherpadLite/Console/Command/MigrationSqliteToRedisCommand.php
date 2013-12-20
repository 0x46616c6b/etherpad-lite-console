<?php

namespace EtherpadLite\Console\Command;

use Predis\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationSqliteToRedisCommand extends Command
{
    protected function configure()
    {
        $this->setName('redis:import:sqlite')
            ->setDescription('Imports a sqlite database to redis')
            ->setDefinition(
                array(
                    new InputArgument('file', InputArgument::REQUIRED, 'The sqlite file'),
                    new InputOption('host', 'H', InputOption::VALUE_OPTIONAL, 'Redis hostname', 'localhost'),
                    new InputOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Redis port', 6379),
                )
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');

        if (!is_file($file)) {
            throw new \Exception(sprintf('File %s not found!', $file));
        }

        $db = new \PDO(sprintf('sqlite:%s', $file));
        $redis = new Client(array(
            'scheme' => 'tcp',
            'host'   => $input->getOption('host'),
            'port'   => $input->getOption('port'),
        ));

        $c = 0;

        foreach ($db->query("SELECT * FROM store") as $row) {
            $parts = explode(':', $row['key']);

            $key = sprintf('ueberDB:keys:%s', $parts[0]);
            $value = sprintf('%s:%s', $parts[0], $parts[1]);
            $redis->sadd($key, $value);

            $redis->set($row['key'], $row['value']);
            $c++;
        }

        if ($output->isVerbose()) {
            $output->writeln(sprintf('%s values imported', $c));
        }
    }
}