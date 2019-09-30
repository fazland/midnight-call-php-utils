<?php declare(strict_types=1);

namespace MidnightCall\Utils;

final class ArrayUtils
{
    public const ASC = 'ASC';
    public const DESC = 'DESC';

    private function __construct()
    {
    }

    /**
     * @param array $input
     *
     * @return array
     */
    public static function permutations(array $input): array
    {
        $args = \func_get_args();

        switch (\count($args)) {
            case 1:
                return $args[0];

            case 0:
                throw new \InvalidArgumentException(\sprintf('%s requires one or more arrays', __METHOD__));
        }

        $a = \array_shift($args);
        $b = \call_user_func_array(__METHOD__, $args);

        $return = [];
        foreach ($a as $v) {
            foreach ($b as $v2) {
                $return[] = \array_merge([$v], (array) $v2);
            }
        }

        return $return;
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    public static function isAssoc(array $array): bool
    {
        return \array_keys($array) !== \array_keys(\array_values($array));
    }

    /**
     * @param array  $array
     * @param string $targetKey
     * @param string $order
     *
     * @return array
     */
    public static function sortByKey(array $array, string $targetKey, string $order = self::ASC): array
    {
        $newArray = [];
        $sortableArray = [];

        if (0 < \count($array)) {
            foreach ($array as $key => $value) {
                if (\is_array($value)) {
                    foreach ($value as $k2 => $v2) {
                        if ($k2 === $targetKey) {
                            $sortableArray[$key] = $v2;
                        }
                    }
                } else {
                    $sortableArray[$key] = $value;
                }
            }

            switch ($order) {
                case self::ASC:
                    \asort($sortableArray);
                    break;

                case self::DESC:
                    \arsort($sortableArray);
                    break;
            }

            foreach ($sortableArray as $key => $value) {
                $newArray[$key] = $array[$key];
            }
        }

        return $newArray;
    }

    /**
     * Recursively filters a multi-dimensional array.
     *
     * @param array         $array
     * @param callable|null $callback
     *
     * @return array
     */
    public static function filterRecursive(array $array, ?callable $callback = null): array
    {
        foreach ($array as $key => &$value) {
            if (\is_array($value)) {
                $value = self::filterRecursive($value, $callback);
            }

            if (empty($value) || (null !== $callback && ! $callback($value, $key))) {
                unset($array[$key]);
            }
        }

        unset($value);

        return $array;
    }
}
