<?php

namespace Helio\Invest\Utility;

use Adbar\Dot;

class ArrayUtility
{
    /**
     * @param array $dataBags
     * @param array $possiblePaths
     * @param mixed|null $default
     * @return mixed|mixed
     */
    public static function getFirstByDotNotation(array $dataBags, array $possiblePaths, $default = null)
    {
        foreach ($dataBags as $bag) {
            $dot = new Dot($bag);
            foreach ($possiblePaths as $path) {
                if ($dot->has($path)) {
                    return $dot->get($path);
                }
            }
        }
        return $default;
    }
}