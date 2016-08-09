<?php

namespace Entity;

use Spot\Entity;
use Spot\MapperInterface;
use Spot\EntityInterface;

/**
 * Class Palette
 * @package Entity
 */
class Palette extends Entity
{
    protected static $table = "palettes";

    /**
     * @return array
     */
    public static function fields()
    {
        return [
            'id'        => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'title'     => ['type' => 'string', 'size' => 255],
            'comment'   => ['type' => 'string', 'size' => 255],
            'columns'   => ['type' => 'integer'],
            'filename'  => ['type' => 'string'],
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
            'colors'    => $mapper->hasMany($entity, 'Entity\Color', 'palette_id'),
        ];
    }
}
