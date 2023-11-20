<?php

namespace Inetris\Nocode\Strategy\Entity;

interface EntityInterface
{
    public static function getEntityName(int $iblockId): string;

    public static function getTableName(int $iblockId): string;

    public function compileEntity();

    public function multiplePropertiesTable($fields): array;
}

