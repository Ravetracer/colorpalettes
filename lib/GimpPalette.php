<?php

namespace Colorpalettes;

use Colorpalettes\BaseColor as BaseColor;
/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 19.01.16
 * Time: 11:41
 */
class GimpPalette extends BasePalette
{
    /**
     * @var string
     */
    private $fileName = "";

    /**
     * @var array
     */
    private $paletteFile = [];

    /**
     * @var bool
     */
    private $validFile = false;

    /**
     * GimpPalette constructor.
     * @param $fileName
     */
    public function __construct($fileName)
    {
        $this->fileName = $fileName;
        if (file_exists($fileName)) {
            $this->paletteFile = file($fileName);
            if (count($this->paletteFile)) {
                $this->parseFile();
            }
        }
    }

    /**
     * Return if readed file is valid
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->validFile;
    }

    /**
     * Get file name of palette file
     *
     * @param bool $base
     * @return string
     */
    public function getFilename($base = true)
    {
        return $base ? basename($this->fileName) : $this->fileName;
    }

    /**
     * Generates the palette content for exporting to GIMP palette file
     *
     * @return string
     */
    public function getExportContents()
    {
        $export = "GIMP Palette\n"
                . "Name: " . $this->getName() . "\n"
                . "Columns: " . $this->getColumns() . "\n"
                . "# " . $this->getComment() . "\n";

        /**
         * @var $currentColor BaseColor
         */
        foreach ($this->getColors() as $currentColor) {
            $export .= str_pad((String)$currentColor->getRed(), 3, " ", STR_PAD_LEFT) . " "
                     . str_pad((String)$currentColor->getGreen(), 3, " ", STR_PAD_LEFT) . " "
                     . str_pad((String)$currentColor->getBlue(), 3, " ", STR_PAD_LEFT) . "\t"
                     . $currentColor->getName() . "\n";
        }

        return $export;
    }
}