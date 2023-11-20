<?php
namespace Inetris\Nocode;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

class Autoload
{
    /**
     * @throws LoaderException
     */
    public function __construct()
    {
        Loader::registerAutoLoadClasses(
            $this->getModuleId(),
            $this->getModuleClasses()
        );
    }

    /**
     * @return string
     */
    public function getModuleId(): string
    {
        return basename(__DIR__);
    }

    /**
     * @return string
     */
    public function getModuleNamespace(): string
    {
        $moduleId = $this->getModuleId();
        $names = explode(".", $moduleId);
        $namespace = "";

        foreach ($names as $name) {
            $namespace .= "\\".ucfirst($name);
        }

        return $namespace;
    }

    /**
     * @param string $path
     * @return array
     */
    public function getModuleClasses(string $path = "lib"): array
    {
        $includedNamespaces = str_replace(["lib", "/"], ["", "\\"], $path);
        $libPath = $path."/";
        $libFiles = scandir(__DIR__."/".$libPath);
        $namespace = $this->getModuleNamespace();
        $moduleClasses = [];

        foreach ($libFiles as $libName) {
            if (substr($libName, 0, 1) == '.') continue;
            if (substr($libName, -4) != ".php") {
                $nextLevelModuleClasses = $this->getModuleClasses($path.'/'.$libName);
                $moduleClasses = array_merge($moduleClasses, $nextLevelModuleClasses);
            } else {
                $class = $namespace.$includedNamespaces."\\".substr($libName, 0, -4);
                $moduleClasses[$class] = $libPath.$libName;
            }
        }

        return $moduleClasses;
    }
}

$autoload = new Autoload();
