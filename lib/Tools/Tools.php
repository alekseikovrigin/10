<?php

namespace Inetris\Nocode\Tools;

class Tools
{
    /**
     * @param string $desiredValue
     * @param array $array
     * @param string $subKey
     * @return int|null
     */
    public static function getParam(string $desiredValue, array $array, string $subKey = "CODE"): ?int
    {
        foreach ($array as $key => $item) {
            if (isset($item[$subKey]) && $item[$subKey] === $desiredValue) {
                return $key;
            }
        }
        return null;
    }

    /**
     * @param array $array
     * @param string $key
     * @return array
     */
    public static function getIndex(array $array, string $key = "ID"): array
    {
        $indexed = array();
        foreach ($array as $item) {
            $indexed[$item[$key]] = $item;
        }
        return $indexed;
    }

    /**
     * @param $needle
     * @param array $array
     * @param bool $all
     * @return void
     */
    public static function removeFromArray($needle, array &$array, bool $all = true): void
    {
        while (FALSE !== $key = array_search($needle, $array)) {
            unset($array[$key]);
            if (!$all) return;
        }
    }

    /**
     * @param $arrays
     * @return array
     */
    public static function mergeArrays($arrays): array
    {
        $result = [];

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                    $result[$key] = self::mergeArrays([$result[$key], $value]);
                } else {
                    if (isset($result[$key]) && !is_numeric($key)) {
                        $result[$key] = array_merge((array)$result[$key], (array)$value);
                    } else {
                        $result[$key] = $value;
                    }
                }
            }
        }

        return $result;
    }
}
