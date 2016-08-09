<?php

namespace Colorpalettes\Interfaces;

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
    public function getParsedColors(): array;

    /**
     * Get the name of the palette
     * @return string
     */
    public function getPaletteName(): string;

    /**
     * Get the base filename of the palette without extension
     * @return string
     */
    public function getFilename(): string;

    /**
     * Get the palette comment
     * @return string
     */
    public function getComment(): string;

    /**
     * Get the number of columns in the palette file
     * @return int
     */
    public function getColumns(): int;

    /**
     * Returns if the given input file is valid
     * @return bool
     */
    public function isValid(): bool;
}