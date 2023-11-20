<?php
namespace Inetris\Nocode;

class Main {
    protected $moduleId;
    public $lastError;

    public function __construct()
    {
        $this->moduleId = basename(__DIR__);
    }

    public function getModuleId(): string
    {
        return $this->moduleId;
    }
}

require_once('autoload.php');
