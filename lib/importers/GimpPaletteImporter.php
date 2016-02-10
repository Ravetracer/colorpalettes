<?php

namespace Colorpalettes\Importers;

use Colorpalettes\ASEDecoder;
use Colorpalettes\Interfaces\ImporterInterface;
use Colorpalettes\BaseColor;
/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 19.01.16
 * Time: 11:41
 */
class GimpPaletteImporter extends BaseImporter implements ImporterInterface
{
    /**
     * @var string
     */
    private $fileName = "";

    /**
     * @var bool
     */
    private $isValid = false;

    /**
     * @var array
     */
    private $paletteFile = [];

    /**
     * @var string
     */
    private $paletteName = '';

    /**
     * @var string
     */
    private $comment = '';

    /**
     * @var int
     */
    private $columns = 1;

    /**
     * GimpPalette constructor.
     * @param $fileName
     */
    public function __construct($fileName)
    {
        $this->fileName = $fileName;
        if (file_exists($fileName)) {
            $this->paletteFile = file($fileName);
            if (strcmp(trim($this->paletteFile[0]), "GIMP Palette") === 0) {
                $this->isValid = true;
            }
        }
    }

    /**
     * Return, if the given file is valid
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * Return palette file name without extension
     *
     * @return string
     */
    public function getFilename()
    {
        return basename($this->fileName, '.ase');
    }

    /**
     * Return file name because ASE files don't have a palette name attribute
     *
     * @return string
     */
    public function getPaletteName()
    {
        return $this->paletteName;
    }

    /**
     * Return empty comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return int
     */
    public function getColumns()
    {
        return (int)$this->columns;
    }

    /**
     * Parse palette file
     *
     * @return bool
     */
    public function getParsedColors()
    {
        if (!$this->isValid()) {
            return false;
        }
        $colors = [];
        foreach ($this->paletteFile as $currentEntry) {
            // fetch name
            preg_match("/(Name|name): (.*)/", $currentEntry, $nameMatch);
            if (count($nameMatch)) {
                $this->paletteName = trim($nameMatch[2]);
            }

            // fetch columns
            preg_match("/(Columns|columns): ([0-9]+)/", $currentEntry, $columnMatch);
            if (count($columnMatch)) {
                $this->columns = (int)$columnMatch[2];
            }

            // fetch comment
            preg_match("/#(.*)/", $currentEntry, $commentMatch);
            if (count($commentMatch)) {
                $this->comment = trim(filter_var($commentMatch[1], FILTER_SANITIZE_STRING));
            }

            // fetch color
            preg_match("/([0-9]{1,3})\s+([0-9]{1,3})\s+([0-9]{1,3})(\s(.*)|)/", trim($currentEntry), $colorMatch);
            if (count($colorMatch)) {
                $newCol = new BaseColor();
                $newCol->setRed((int)$colorMatch[1])
                    ->setGreen((int)$colorMatch[2])
                    ->setBlue((int)$colorMatch[3])
                    ->setName($colorMatch[4]);

                $colors[] = $newCol;
            }
        }
        return $colors;
    }
}