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
        $app['db']->exec('DROP TABLE palettes');
        $app['db']->exec('DROP TABLE colors');
        $mapper = $app['spot']->mapper('Entity\Palette');
        $mapper->migrate();
        $mapper2 = $app['spot']->mapper('Entity\Color');
        $mapper2->migrate();
        $output->writeln('Done...');
    }
}