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
        $this->setName('sqlite-to-redis')
            ->setDescription('Migrate a sqlite db to redis')
            ->setDefinition(
                array(
                    new InputArgument('file', InputArgument::REQUIRED, 'The API Key of your Etherpad Instance'),
                    new InputOption('host',     'H', InputOption::VALUE_OPTIONAL, 'Redis hostname', 'localhost'),
                    new InputOption('port',     'p', InputOption::VALUE_OPTIONAL, 'Redis port', 6379),
                    new InputOption('database', 'd', InputOption::VALUE_OPTIONAL, 'Redis database', '0')
                )
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!is_file($input->getArgument('file'))) {
            throw new \Exception(sprintf('File %s not found!', $input->getOption('file')));
        }

        $db = new \PDO(sprintf('sqlite:%s', $input->getOption('file')));
        $redis = new Client(array(
            'scheme' => 'tcp',
            'host'   => $input->getOption('host'),
            'port'   => $input->getOption('port'),
        ));

        foreach ($db->query("SELECT * FROM store") as $row) {
            $redis->set($row['key'], $row['value']);
        }
    }
}