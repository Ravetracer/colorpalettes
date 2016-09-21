<?php
/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 09.08.16
 * Time: 17:05
 */

namespace Colorpalettes;

/**
 * Class DatabaseHelper
 * @package Colorpalettes
 */
class DatabaseHelper
{
    /**
     * @param SilexApp $app
     * @param int      $offset
     * @param int      $limit
     * @return array
     */
    public static function getLimitedEntries($app, int $offset, int $limit): array
    {
        $pals = [];
        $mapper = $app['spot']->mapper('Entity\Palette');
        $number = $mapper->all()->count();
        if ($offset > $number) {
            return $pals;
        }
        $result = $mapper->all()->order(['filename' => 'ASC'])->limit($limit, $offset);

        $resultConv = $app["result_to_array"];
        $pals = $resultConv->getPaletteArray($result);

        return $pals;
    }

    /**
     * Get the number of current palettes
     *
     * @param SilexApp $app
     * @return int
     */
    public static function getPaletteCount($app): int
    {
        $mapper = $app['spot']->mapper('Entity\Palette');
        $num = $mapper->all()->count();

        return $num;

    }
}
