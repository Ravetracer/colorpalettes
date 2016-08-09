<?php
/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 02.08.16
 * Time: 18:26
 */

namespace Colorpalettes\Exporters;

use Colorpalettes\BasePalette;
use Colorpalettes\BaseColor;
use Colorpalettes\Interfaces\ExporterInterface;
use Colorpalettes\ACOEncoder;

/**
 * Class AdobeColorfileExporter
 * @package Colorpalettes\Exporters
 */
class AdobeColorfileExporter implements ExporterInterface
{
    /**
     * @var BasePalette
     */
    private $palette = null;

    /**
     * AdobeColorfileExporter constructor.
     * @param BasePalette $palette
     */
    public function __construct(BasePalette $palette)
    {
        $this->palette = $palette;
    }

    /**
     * Generate .aco file contents
     *
     * @return string
     */
    public function getExportContents(): string
    {
        $acoPal = new ACOEncoder();
        /**
         * @var BaseColor $currentColor
         */
        foreach ($this->palette->getColors() as $currentColor) {
            $acoPal->add(
                $currentColor->getName(),
                $currentColor->getRed(),
                $currentColor->getGreen(),
                $currentColor->getBlue()
            );
        }

        return $acoPal->createAcofile();
    }

    /**
     * @return string
     */
    public function getExportFileExtension(): string
    {
        return 'aco';
    }
}