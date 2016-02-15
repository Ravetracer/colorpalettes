<?php

namespace Commands;

use Knp\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Spot\Locator,
    Entities\Palette;

class ImportPalsCommand extends Command
{

    /**
     * @var Locator
     */
    private $spot = null;

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->spot = require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR. 'db_conf.php';
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('colpal:importfiles')
             ->setDescription('Import .gpl files into the database.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $palMapper = $this->spot->mapper('Entities\Palette');
        $palMapper->all();
        $output->writeln('Works...');
    }
}