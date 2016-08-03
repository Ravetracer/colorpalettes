<?php

namespace Entity;

use Spot\Entity,
    Spot\MapperInterface,
    Spot\EntityInterface;

class Palette extends Entity
{
    protected static $table = "palettes";

    public static function fields()
    {
        return [
            'id'        => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'title'     => ['type' => 'string', 'size' => 255],
            'comment'   => ['type' => 'string', 'size' => 255],
            'columns'   => ['type' => 'integer'],
            'filename'  => ['type' => 'string']
        ];
    }

    public static function relations(MapperInterface $mapper, EntityInterface $entity)
    {
        return [
            'colors'    => $mapper->hasMany($entity, 'Entity\Color', 'palette_id')
        ];
    }
}