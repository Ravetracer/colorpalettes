<?php

namespace Colorpalettes\Exporters;

use Colorpalettes\BasePalette,
    Colorpalettes\BaseColor,
    Colorpalettes\Interfaces\ExporterInterface;

/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 19.01.16
 * Time: 11:41
 */
class GimpPaletteExporter implements ExporterInterface
{
    /**
     * @var BasePalette|null
     */
    private $palette = null;

    /**
     * GimpPalette constructor.
     *
     * @param BasePalette $palette
     */
    public function __construct(BasePalette $palette)
    {
        $this->palette = $palette;
    }

    /**
     * Generates the palette content for exporting to GIMP palette file
     *
     * @return string
     */
    public function getExportContents()
    {
        $export = "GIMP Palette\n"
                . "Name: " . $this->palette->getName() . "\n"
                . "Columns: " . $this->palette->getColumns() . "\n"
                . "# " . $this->palette->getComment() . "\n";

        /**
         * @var $currentColor BaseColor
         */
        foreach ($this->palette->getColors() as $currentColor) {
            $export .= str_pad((String)$currentColor->getRed(), 3, " ", STR_PAD_LEFT) . " "
                     . str_pad((String)$currentColor->getGreen(), 3, " ", STR_PAD_LEFT) . " "
                     . str_pad((String)$currentColor->getBlue(), 3, " ", STR_PAD_LEFT) . "\t"
                     . $currentColor->getName() . "\n";
        }
        return $export;
    }

    /**
     * @return string
     */
    public function getExportFileExtension()
    {
        return 'gpl';
    }
}