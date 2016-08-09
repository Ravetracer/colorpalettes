<?php
/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 09.02.16
 * Time: 08:20
 */

namespace Colorpalettes;

use Symfony\Component\HttpFoundation\Response;
use Colorpalettes\Interfaces\ImporterInterface;
use Colorpalettes\Interfaces\ExporterInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class BasePalette
 * @package Colorpalettes
 */
class BasePalette
{
    protected $comment = "";
    protected $name = "";
    protected $columns = 1;
    protected $colors = [];
    protected $colorCount = 0;
    protected $filename = "";
    protected $id = 0;

    /**
     * @param int $id
     * @return BasePalette
     */
    public function setId($id = 0): BasePalette
    {
        $this->id = (int) $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->id;
    }

    /**
     * Get number of columns for raster view
     *
     * @return int
     */
    public function getColumns(): int
    {
        return $this->columns;
    }

    /**
     * @param int $cols
     * @return BasePalette
     */
    public function setColumns($cols = 1): BasePalette
    {
        $this->columns = (int) $cols;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return BasePalette
     */
    public function setComment($comment = ''): BasePalette
    {
        $this->comment = trim(filter_var($comment, FILTER_SANITIZE_STRING));

        return $this;
    }


    /**
     * Get name of palette
     *
     * @return string
     */
    public function getName(): string
    {
        return strlen($this->name) ? $this->name : str_replace(' ', '_', $this->getFilename());
    }

    /**
     * @param string $name
     * @return BasePalette
     */
    public function setName($name = ''): BasePalette
    {
        $this->name = filter_var($name, FILTER_SANITIZE_STRING);

        return $this;
    }

    /**
     * Get color array
     *
     * @return array
     */
    public function getColors(): array
    {
        return $this->colors;
    }

    /**
     * Set new color array (e.g. from foreign palette format)
     *
     * @param array $colorArray
     * @return BasePalette
     */
    public function setColors(array $colorArray = []): BasePalette
    {
        $this->colors = $colorArray;

        return $this;
    }

    /**
     * Import a palette file
     *
     * @param ImporterInterface $importer
     * @return bool
     */
    public function import(ImporterInterface $importer): bool
    {
        if ($importer->isValid()) {
            $this->setColors($importer->getParsedColors())
                 ->setName($importer->getPaletteName())
                 ->setComment($importer->getComment())
                 ->setFilename($importer->getFilename())
                 ->setColumns($importer->getColumns());

            return true;
        }

        return false;
    }

    /**
     * Export palette
     *
     * @param ExporterInterface $exporter
     * @return Response
     */
    public function export(ExporterInterface $exporter): Response
    {
        $contents = $exporter->getExportContents();
        $response = new Response($contents);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $this->getFilename().'.'.$exporter->getExportFileExtension()
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return BasePalette
     */
    public function setFilename($filename): BasePalette
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Calculates the exact color count of different colors
     *
     * @return BasePalette
     */
    public function calculateColorCount(): BasePalette
    {
        $colCount = 0;
        $existingCombinations = [];
        /**
         * @var $currentColor BaseColor
         */
        foreach ($this->getColors() as $currentColor) {
            $currentCombination = [
                $currentColor->getRed(),
                $currentColor->getGreen(),
                $currentColor->getBlue(),
            ];
            if (!in_array($currentCombination, $existingCombinations)) {
                $colCount++;
                $existingCombinations[] = $currentCombination;
            }
        }
        $this->colorCount = $colCount;

        return $this;
    }

    /**
     * Get the color count
     *
     * @return int
     */
    public function getColorCount(): int
    {
        if ($this->colorCount <= 0) {
            $this->calculateColorCount();
        }

        return (int) $this->colorCount;
    }
}
