<?php

namespace Entity;

use Spot\Entity;
use Spot\MapperInterface;
use Spot\EntityInterface;

/**
 * Class Color
 * @package Entity
 */
class Color extends Entity
{
    protected static $table = "colors";

    /**
     * @return array
     */
    public static function fields()
    {
        return [
            'id'            => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'title'         => ['type' => 'string', 'size' => 255],
            'red_value'     => ['type' => 'integer', 'default' => 0, 'required' => true],
            'green_value'   => ['type' => 'integer', 'default' => 0, 'required' => true],
            'blue_value'    => ['type' => 'integer', 'default' => 0, 'required' => true],
            'palette_id'    => ['type' => 'integer', 'required' => true],
        ];
    }

    /**
     * @param MapperInterface $mapper
     * @param EntityInterface $entity
     * @return array
     */
    public static function relations(MapperInterface $mapper, EntityInterface $entity)
    {
        return [
            'user' => $mapper->belongsTo($entity, 'Entity\Palette', 'palette_id'),
        ];
    }
}
