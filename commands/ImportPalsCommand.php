<?php

namespace Commands;

use Colorpalettes\BaseColor,
    Knp\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Colorpalettes\BasePalette,
    Colorpalettes\Importers\GimpPaletteImporter;

class ImportPalsCommand extends Command
{

    public function __construct($name = null)
    {
        parent::__construct($name);
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
        $app = $this->getSilexApplication();

        $palFiles = glob('./import/*.gpl');

        asort($palFiles);

        $mapper_pal = $app['spot']->mapper('Entity\Palette');
        $mapper_col = $app['spot']->mapper('Entity\Color');
        foreach ($palFiles as $currentFile) {
            $palObj = new BasePalette();
            $palObj->import(new GimpPaletteImporter($currentFile));

            if ($palObj->getColumns() == 1) {
                $palObj->setColumns(16);
            }

            $newPalette = $mapper_pal->create([
                'title'     => $palObj->getName(),
                'comment'   => $palObj->getComment(),
                'columns'   => $palObj->getColumns(),
                'filename'  => $palObj->getFilename()
            ]);

            /**
             * @var BaseColor $currentColor
             */
            foreach ($palObj->getColors() as $currentColor) {
                $mapper_col->create([
                    'title'         => $currentColor->getName(),
                    'red_value'     => $currentColor->getRed(),
                    'green_value'   => $currentColor->getGreen(),
                    'blue_value'    => $currentColor->getBlue(),
                    'palette_id'    => $newPalette->id,
                ]);
            }

            $output->writeln("Processed: " . $currentFile);
        }

    }
}