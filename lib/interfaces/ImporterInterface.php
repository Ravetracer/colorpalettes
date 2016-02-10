<?php

namespace Colorpalettes\Interfaces;

interface ImporterInterface
{
    /**
     * ImporterInterface constructor.
     * @param $filename
     */
    public function __construct($filename);

    /**
     * Returns the parsed color array
     *
     * @return array
     */
    public function getParsedColors();

    /**
     * Returns the name of the palette
     *
     * @return string
     */
    public function getPaletteName();

    /**
     * Returns the comment
     *
     * @return string
     */
    public function getComment();

    /**
     * Returns the base filename without extension
     *
     * @return string
     */
    public function getFilename();

    /**
     * Return the number of columns
     * 
     * @return mixed
     */
    public function getColumns();

    /**
     * Returns, if the given file is valid
     *
     * @return boolean
     */
    public function isValid();
}