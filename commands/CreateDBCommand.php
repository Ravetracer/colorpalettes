<?php

namespace Commands;

use Knp\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

class CreateDBCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('colpal:createdb')
             ->setDescription('Creates the DB tables');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();
        $query = "CREATE TABLE colors (id INTEGER PRIMARY KEY ASC, value_red INTEGER DEFAULT 0, value_green INTEGER DEFAULT 0, value_blue INTEGER DEFAULT 0, title TEXT)";
        $app['db']->exec($query);
        $output->writeln('Works...');
    }
}