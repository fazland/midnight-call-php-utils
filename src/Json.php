<?php declare(strict_types=1);

namespace MidnightCall\Utils;

final class Json
{
    /**
     * Decodes a JSON string into an object/array.
     *
     * @param string $json
     * @param bool   $associative
     *
     * @return mixed
     */
    public static function decode(string $json, bool $associative = true)
    {
        return \json_decode($json, $associative, 512, JSON_THROW_ON_ERROR);
    }
}
