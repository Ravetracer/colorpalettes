<?php
/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 16.02.16
 * Time: 14:04
 */

namespace Colorpalettes;

use Spot\Query;

class ResultToArrayService
{
    public function getPaletteArray(Query $dbResult)
    {
        $pals = [];
        foreach ($dbResult as $currentResult) {
            $palColors = [];
            $cols = $currentResult->colors;
            foreach ($cols as $currentColor) {
                $newColor = new BaseColor();
                $newColor->setName($currentColor->title)
                    ->setRed($currentColor->red_value)
                    ->setGreen($currentColor->green_value)
                    ->setBlue($currentColor->blue_value);
                $palColors[] = $newColor;
            }
            $newPal = new BasePalette();
            $newPal->setName($currentResult->title)
                ->setColors($palColors)
                ->setColumns($currentResult->columns)
                ->setComment($currentResult->comment)
                ->setFilename($currentResult->filename)
                ->setId($currentResult->id)
                ->calculateColorCount();

            $pals[] = $newPal;
        }
        return $pals;
    }
}