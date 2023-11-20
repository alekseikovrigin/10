<?php

namespace Inetris\Nocode\Strategy\DataType;

interface DataTypeInterface
{
    public function handle($query, $iblockID = null, $field = null);
}
