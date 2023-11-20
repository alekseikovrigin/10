<?php

namespace Inetris\Nocode\Service;

use Inetris\Nocode\Strategy\Entity;
use Inetris\Nocode\Strategy\Entity\EntityInterface;

class EntityStrategyFactory
{
    /**
     * @param $type
     * @param $iblockId
     * @param $fields
     * @return EntityInterface
     */
    public static function getStrategy($type, $iblockId, $fields = null): Entity\EntityInterface
    {
        switch ($type) {
            case 'single':
                return new Entity\SingleEntityStrategy($iblockId, $fields);
            case 'multiple':
                return new Entity\MultipleEntityStrategy($iblockId, $fields);
            case 'element':
                return new Entity\ElementEntityStrategy($iblockId, $fields);
            default:
                throw new Exception("Unknown strategy type: $type");
        }
    }
}
