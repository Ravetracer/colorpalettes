<?php

namespace Colorpalettes\Importers;

use Colorpalettes\ASEDecoder;
use Colorpalettes\BaseColor;
use Colorpalettes\Interfaces\ImporterInterface;

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
     * @param string $fileName
     */
    public function __construct(string $fileName)
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
        return strtolower(trim(str_replace(' ', '_', basename($this->fileName, '.ase'))));
    }

    /**
     * Return file name because ASE files don't have a palette name attribute
     *
     * @return string
     */
    public function getPaletteName(): string
    {
        return $this->getFilename();
    }

    /**
     * Return empty comment
     *
     * @return string
     */
    public function getComment(): string
    {
        return '';
    }

    /**
     * @return int
     */
    public function getColumns(): int
    {
        return 1;
    }

    /**
     * Get the parsed color array
     *
     * @return array
     */
    public function getParsedColors(): array
    {
        $colors = [];

        if (!$this->isValid()) {
            return $colors;
        }

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