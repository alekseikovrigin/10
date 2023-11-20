<?php

namespace Inetris\Nocode\DTO;

class Settings
{
    public $context;
    public $targetIblockId;
    public $rulesCategory;
    public $cacheTime;
    public $maxCount;
    public $forMassMode;


    public function __construct(
        array  $context,
        int    $targetIblockId,
        string $rulesCategory,
        int    $cacheTime = 3600,
        int    $maxCount = 10,
        bool   $forMassMode = false
    )
    {
        $this->context = $context;
        $this->targetIblockId = $targetIblockId;
        $this->rulesCategory = $rulesCategory;
        $this->cacheTime = $cacheTime;
        $this->maxCount = $maxCount;
        $this->forMassMode = $forMassMode;
    }

}
