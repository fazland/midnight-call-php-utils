<?php declare(strict_types=1);

namespace MidnightCall\Utils;

class StringUtils
{
    private function __construct()
    {
    }

    /**
     * Slugifies a string.
     *
     * @param string $word
     *
     * @return string
     */
    public static function slugify(string $word): string
    {
        $word = \iconv('UTF-8', 'ASCII//TRANSLIT', $word);
        $word = \preg_replace('~([^a-z0-9]+$)~i', '', $word);

        return \mb_strtolower(\preg_replace('~([^a-z0-9]+)~i', '-', $word));
    }
}
