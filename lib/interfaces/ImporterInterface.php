<?php

namespace Colorpalettes\interfaces;

/**
 * Interface ImporterInterface
 * @package Colorpalettes\Interfaces
 */
interface ImporterInterface
{
    /**
     * Get the parsed color palette as an array
     * @return array
     */
    public function getParsedColors();

    /**
     * Get the name of the palette
     * @return string
     */
    public function getPaletteName();

    /**
     * Get the base filename of the palette without extension
     * @return string
     */
    public function getFilename();

    /**
     * Get the palette comment
     * @return string
     */
    public function getComment();

    /**
     * Get the number of columns in the palette file
     * @return int
     */
    public function getColumns();

    /**
     * Returns if the given input file is valid
     * @return boolean
     */
    public function isValid();
}