<?php

namespace Inetris\Nocode\Service;

use Inetris\Nocode\Strategy\DataType;

class FieldStrategyFactory
{
    public static function getStrategy($type, $iblockId, $fields = null): DataType\DataTypeInterface
    {
        switch ($type) {
            case 'single':
                return new DataType\SingleStrategy($iblockId, $fields);
            case 'multiple':
                return new DataType\MultipleStrategy($iblockId, $fields);
            case 'element':
                return new DataType\ElementEntityStrategy($iblockId, $fields);
            default:
                throw new \Exception("Unknown strategy type: $type");
        }
    }
}
