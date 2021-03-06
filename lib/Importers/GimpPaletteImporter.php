<?php

namespace Colorpalettes\Importers;

use Colorpalettes\BaseColor;
use Colorpalettes\Interfaces\ImporterInterface;

/**
 * Class GimpPaletteImporter
 * @package Colorpalettes\importers
 */
class GimpPaletteImporter implements ImporterInterface
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
     * @param string $fileName
     */
    public function __construct(string $fileName)
    {
        if (file_exists($fileName)) {
            $this->fileName = $fileName;
            $this->paletteFile = file($fileName);
            if (strcmp(trim($this->paletteFile[0]), "GIMP Palette") === 0) {
                $this->isValid = true;
            }
        } elseif ($this->isPaletteString($fileName)) {
            $this->paletteFile = explode('\n', $fileName);
            $this->isValid = true;
        }
    }

    /**
     * Return, if the given file is valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * Return palette file name without extension
     *
     * @return string
     */
    public function getFilename(): string
    {
        return strtolower(trim(str_replace(' ', '_', basename($this->fileName, '.gpl'))));
    }

    /**
     * Return file name because ASE files don't have a palette name attribute
     *
     * @return string
     */
    public function getPaletteName(): string
    {
        return $this->paletteName;
    }

    /**
     * Return empty comment
     *
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @return int
     */
    public function getColumns(): int
    {
        return (int) $this->columns;
    }

    /**
     * Parse palette file
     *
     * @return array
     */
    public function getParsedColors(): array
    {
        $colors = [];

        if (!$this->isValid()) {
            return $colors;
        }

        foreach ($this->paletteFile as $currentEntry) {
            // fetch name
            preg_match("/(Name|name): (.*)/", $currentEntry, $nameMatch);
            if (count($nameMatch)) {
                $this->paletteName = trim(filter_var($nameMatch[2], FILTER_SANITIZE_STRING));
                if (strlen($this->fileName) <= 0) {
                    $this->fileName = str_replace(' ', '_', $this->paletteName).'.gpl';
                }
            }

            // fetch columns
            preg_match("/(Columns|columns): ([0-9]+)/", $currentEntry, $columnMatch);
            if (count($columnMatch)) {
                $this->columns = (int) $columnMatch[2];
            }

            // fetch comment
            preg_match("/^#(.*)/", $currentEntry, $commentMatch);
            if (count($commentMatch)) {
                $this->comment .= trim(filter_var($commentMatch[1], FILTER_SANITIZE_STRING))."\n";
            }

            // fetch color
            preg_match("/([0-9]{1,3})\s+([0-9]{1,3})\s+([0-9]{1,3})(\s(.*)|)/", trim($currentEntry), $colorMatch);
            if (count($colorMatch)) {
                $newCol = new BaseColor();
                $newCol->setRed((int) $colorMatch[1])
                    ->setGreen((int) $colorMatch[2])
                    ->setBlue((int) $colorMatch[3])
                    ->setName(trim(filter_var($colorMatch[4], FILTER_SANITIZE_STRING)));

                $colors[] = $newCol;
            }
        }

        return $colors;
    }

    /**
     * @param $inputString
     * @return string
     */
    private function isPaletteString(string $inputString): string
    {
        return strstr($inputString, "GIMP Palette");
    }
}
