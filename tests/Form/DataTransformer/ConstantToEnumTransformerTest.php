<?php declare(strict_types=1);

namespace MidnightCall\Utils\Tests\Form\DataTransformer\MailingList;

use MidnightCall\Utils\Form\DataTransformer\ConstantToEnumTransformer;
use MidnightCall\Utils\Tests\Fixtures\Enum\FooBarEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ConstantToEnumTransformerTest extends TestCase
{
    /**
     * @var ConstantToEnumTransformer
     */
    private $transformer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->transformer = new ConstantToEnumTransformer(FooBarEnum::class);
    }

    public function emptyValues(): iterable
    {
        yield [''];
        yield [null];
    }

    /**
     * @dataProvider emptyValues
     */
    public function testReverseTransformShouldReturnNullOnEmptyValues($value): void
    {
        self::assertNull($this->transformer->reverseTransform($value));
    }

    public function testReverseTransformShouldNotActIfValueIsType(): void
    {
        foreach (FooBarEnum::values() as $value) {
            self::assertEquals($this->transformer->reverseTransform($value), $value);
        }
    }

    public function invalidValues(): iterable
    {
        yield [0.23];
        yield [47];
        yield [['invalid_value']];
        yield ['invalid_value'];
        yield [new \stdClass()];
    }

    /**
     * @dataProvider invalidValues
     */
    public function testReverseTransformShouldThrowOnInvalidValues($value): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->transformer->reverseTransform($value);
    }

    public function testReverseTransformShouldWork(): void
    {
        $reflectionClass = new \ReflectionClass(FooBarEnum::class);
        foreach ($reflectionClass->getConstants() as $constantName => $value) {
            self::assertEquals($this->transformer->reverseTransform($value), FooBarEnum::$constantName());
        }
    }
}
