<?php

namespace commands;

use Colorpalettes\BaseColor;
use Colorpalettes\Exporters\GimpPaletteExporter;
use Knp\Command\Command;
use MischiefCollective\ColorJizz\Formats\Hex;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Colorpalettes\BasePalette;
use Colorpalettes\Importers\GimpPaletteImporter;

/**
 * Class ImportPalsCommand
 * @package commands
 */
class ImportOpenColorCommand extends Command
{
    /**
     * ImportPalsCommand constructor.
     * @param string $name
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('colpal:import-open-color')
             ->setDescription('Imports a special SCSS file from Open Color for color blind people: https://yeun.github.io/open-color/');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();

        $colorFile = file('./import/open-color.scss');

        $mapperPal = $app['spot']->mapper('Entity\Palette');
        $mapperCol = $app['spot']->mapper('Entity\Color');

        // Array to store black and white for later adding to the end of the file
        $bwCols = [];

        $currentMainColor = '';

        $palObj = new BasePalette();
        $palObj->setColumns(10)
            ->setName('Open Color Palette')
            ->setComment('Palette for color blind people taken from https://yeun.github.io/open-color');
        foreach ($colorFile as $currentLine) {
            //
            // catch black and white colors
            //
            if (preg_match('/\$oc\-(white|black):\s+(#[0-9a-f]+);/', $currentLine, $matches)) {
                $colorName = $matches[1];
                $colorHex = $matches[2];
                $color = Hex::fromString($colorHex)->toRGB();

                $newCol = new BaseColor();
                $newCol->setName($colorName)
                    ->setRed($color->getRed())
                    ->setGreen($color->getGreen())
                    ->setBlue($color->getBlue());
                $bwCols[] = $newCol;
            }

            // catch next main color name
            if (preg_match('/\$oc\-([a-z]+)\-list: \(/', $currentLine, $matches)) {
                if ($matches[1] !== "color" && $matches[1] !== $currentMainColor) {
                    $currentMainColor = $matches[1];
                }
            }

            // catch colors for the current main color
            if (preg_match('/\s+"([0-9])"\: (#[0-9a-f]+)/', $currentLine, $matches)) {
                $newCol = new BaseColor();
                $color = Hex::fromString($matches[2])->toRGB();
                $newCol->setName($currentMainColor.'-'.$matches[1])
                    ->setRed($color->getRed())
                    ->setGreen($color->getGreen())
                    ->setBlue($color->getBlue());
                $palObj->addColor($newCol);
            }
        }

        // adding black and white to the end
        foreach ($bwCols as $currentColor) {
            $palObj->addColor($currentColor);
        }

        /**
         * Adding palette to database
         */
        $newPalette = $mapperPal->create([
            'title' => $palObj->getName(),
            'comment' => $palObj->getComment(),
            'columns' => $palObj->getColumns(),
            'filename' => $palObj->getFilename(),
        ]);

        /**
         * Adding
         */
        foreach ($palObj->getColors() as $currentColor) {
            $mapperCol->create([
                'title' => $currentColor->getName(),
                'red_value' => $currentColor->getRed(),
                'green_value' => $currentColor->getGreen(),
                'blue_value' => $currentColor->getBlue(),
                'palette_id' => $newPalette->id,
            ]);
        }
    }
}
