<?php
namespace Colorpalettes\Interfaces;

use Colorpalettes\BasePalette;

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
