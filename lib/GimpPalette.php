<?php

namespace Colorpalettes;

/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 19.01.16
 * Time: 11:41
 */
class GimpPalette
{
    private $fileName = "";
    private $paletteFile = [];

    private $comment = "";
    private $name = "";
    private $columns = 1;
    private $colors = [];

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
     * Parse palette file
     *
     * @return bool
     */
    private function parseFile()
    {
        /**
         * don't parse if header fails
         */
        if (strcmp(trim($this->paletteFile[0]), "GIMP Palette") != 0) {
            $this->paletteFile = [];
            return false;
        }
        $this->validFile = true;

        foreach ($this->paletteFile as $currentEntry) {
            // fetch name
            preg_match("/(Name|name): (.*)/", $currentEntry, $nameMatch);
            if (count($nameMatch)) {
                $this->name = trim($nameMatch[2]);
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
                $r = (int)$colorMatch[1];
                $g = (int)$colorMatch[2];
                $b = (int)$colorMatch[3];
                $this->colors[] = [
                    "r"         => $r,
                    "g"         => $g,
                    "b"         => $b,
                    "colhex"    => sprintf("%02x%02x%02x", $r, $g, $b),
                    "name"      => $colorMatch[4],
                ];
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
     * Get number of columns for raster view
     *
     * @return int
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Get name of palette
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Get color array
     *
     * @return array
     */
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * @param int $cols
     */
    public function setColumns($cols = 1)
    {
        $this->columns = (int)$cols;
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

        foreach ($this->getColors() as $currentColor) {
            $export .= str_pad((String)$currentColor["r"], 3, " ", STR_PAD_LEFT) . " "
                     . str_pad((String)$currentColor["g"], 3, " ", STR_PAD_LEFT) . " "
                     . str_pad((String)$currentColor["b"], 3, " ", STR_PAD_LEFT) . "\t"
                     . $currentColor["name"] . "\n";
        }

        return $export;
    }
}