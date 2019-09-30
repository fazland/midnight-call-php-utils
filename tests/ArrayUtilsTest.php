<?php declare(strict_types=1);

namespace MidnightCall\Utils\Tests;

use MidnightCall\Utils\ArrayUtils;
use PHPUnit\Framework\TestCase;

class ArrayUtilsTest extends TestCase
{
    public function testPermutationsShouldWork(): void
    {
        $res = ArrayUtils::permutations(['Prova', 'Foo'], ['Bar', 'Barretta'], ['BarBar', 'FooBar'], ['Bih', 'Buh']);

        self::assertEquals([
            ['Prova', 'Bar', 'BarBar', 'Bih'],
            ['Prova', 'Bar', 'BarBar', 'Buh'],

            ['Prova', 'Bar', 'FooBar', 'Bih'],
            ['Prova', 'Bar', 'FooBar', 'Buh'],

            ['Prova', 'Barretta', 'BarBar', 'Bih'],
            ['Prova', 'Barretta', 'BarBar', 'Buh'],
            ['Prova', 'Barretta', 'FooBar', 'Bih'],
            ['Prova', 'Barretta', 'FooBar', 'Buh'],

            ['Foo', 'Bar', 'BarBar', 'Bih'],
            ['Foo', 'Bar', 'BarBar', 'Buh'],
            ['Foo', 'Bar', 'FooBar', 'Bih'],
            ['Foo', 'Bar', 'FooBar', 'Buh'],
            ['Foo', 'Barretta', 'BarBar', 'Bih'],
            ['Foo', 'Barretta', 'BarBar', 'Buh'],
            ['Foo', 'Barretta', 'FooBar', 'Bih'],
            ['Foo', 'Barretta', 'FooBar', 'Buh'],
        ], $res);
    }

    public function arrayIsAssocProvider(): array
    {
        return [
            [false, []],
            [false, ['a', 'b']],
            [true, ['a' => 0, 'b' => 1]],
            [false, [0, 1, 4, 5]],
            [true, [0 => 'a', 2 => 'b']],
        ];
    }

    /**
     * @dataProvider arrayIsAssocProvider
     */
    public function testIsAssocShouldWork(bool $expected, array $input): void
    {
        self::assertEquals($expected, ArrayUtils::isAssoc($input));
    }

    public function filterArrayProvider(): iterable
    {
//        yield [
//            ['foo' => 'bar', 'foobar' => [ 'baz' => 'baz' ]],
//            ['test' => '', 'foo' => 'bar', 'foobar' => [ 'bar' => null, 'baz' => 'baz' ]],
//        ];

        yield [
            ['foobar' => ['baz' => 'baz']],
            ['test' => ['foofoo' => ['foo' => 0]], 'foobar' => ['bar' => null, 'baz' => 'baz']],
        ];
    }

    /**
     * @dataProvider filterArrayProvider
     */
    public function testFilterRecursiveShouldWork(array $expected, array $input): void
    {
        self::assertEquals($expected, ArrayUtils::filterRecursive($input));
    }
}
