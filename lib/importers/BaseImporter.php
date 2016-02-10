<?php

namespace Colorpalettes\Importers;

use Colorpalettes\Interfaces\ImporterInterface;

abstract class BaseImporter implements ImporterInterface
{
    /**
     * @inheritdoc
     */
    abstract public function getParsedColors();

    /**
     * @inheritdoc
     */
    abstract public function getPaletteName();

    /**
     * @inheritdoc
     */
    abstract public function getFilename();

    /**
     * @inheritdoc
     */
    abstract public function getComment();

    /**
     * @inheritdoc
     */
    abstract public function getColumns();
}