<?php

namespace Arrilot\DataAnonymization;

class Helpers
{
    /**
     * Array only.
     *
     * @param array        $array
     * @param array|string $keys
     *
     * @return array
     */
    public static function arrayOnly($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }
}
