<?php

namespace Colorpalettes\Importers;

use Colorpalettes\ASEDecoder,
    Colorpalettes\BaseColor,
    Colorpalettes\Interfaces\ImporterInterface;

/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 19.01.16
 * Time: 11:41
 */
class AdobeSwatchExchangeImporter implements ImporterInterface
{
    /**
     * @var string
     */
    private $fileName = "";

    /**
     * @var array
     */
    private $swatches = [];

    /**
     * @var bool
     */
    private $isValid = false;

    /**
     * GimpPalette constructor.
     * @param $fileName
     */
    public function __construct($fileName)
    {
        $this->fileName = $fileName;
        if (file_exists($fileName)) {
            $this->swatches = ASEDecoder::decodeFile($fileName);
            $this->isValid = true;
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
        return strtolower(trim(str_replace(' ', '_', basename($this->fileName, '.ase'))));
    }

    /**
     * Return file name because ASE files don't have a palette name attribute
     *
     * @return string
     */
    public function getPaletteName()
    {
        return $this->getFilename();
    }

    /**
     * Return empty comment
     *
     * @return string
     */
    public function getComment()
    {
        return '';
    }

    /**
     * @return int
     */
    public function getColumns()
    {
        return 1;
    }

    /**
     * Get the parsed color array
     *
     * @return array
     */
    public function getParsedColors()
    {
        $colors = [];
        foreach ($this->swatches as $currentEntry) {
            $col = new BaseColor();
            $col->setRed($currentEntry['r'])
                ->setGreen($currentEntry['g'])
                ->setBlue($currentEntry['b'])
                ->setName($currentEntry['title']);
            $colors[] = $col;
        }
        return $colors;
    }
}