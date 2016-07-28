<?php
namespace Colorpalettes\interfaces;

use Colorpalettes\BasePalette;

/**
 * Interface ExporterInterface
 * @package Colorpalettes\interfaces
 */
interface ExporterInterface
{
    /**
     * BaseExporter constructor.
     *
     * @param BasePalette $palette
     */
    public function __construct(BasePalette $palette);

    /**
     * Get export contents for the file
     *
     * @return mixed
     */
    public function getExportContents();

    /**
     * Returns the file extension
     *
     * @return string
     */
    public function getExportFileExtension();
}
