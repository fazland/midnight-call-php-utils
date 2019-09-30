<?php declare(strict_types=1);

namespace MidnightCall\Utils\Form\DataTransformer;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\Exception\TransformationFailedException;

trait TypeAssertionTrait
{
    /**
     * Throws a {@see TransformationFailedException} if $value is not an array.
     *
     * @param mixed $value
     */
    public function assertArray($value): void
    {
        if (\is_array($value)) {
            return;
        }

        throw new TransformationFailedException($this->buildExceptionMessage($value, 'array'));
    }

    /**
     * Throws a {@see TransformationFailedException} if $value is not a string.
     *
     * @param mixed $value
     */
    public function assertString($value): void
    {
        if (\is_string($value)) {
            return;
        }

        throw new TransformationFailedException($this->buildExceptionMessage($value, 'string'));
    }

    /**
     * Throws a {@see TransformationFailedException} if $value is not a numeric value.
     *
     * @param mixed $value
     */
    public function assertNumeric($value): void
    {
        if (\is_numeric($value)) {
            return;
        }

        throw new TransformationFailedException($this->buildExceptionMessage($value, 'numeric'));
    }

    /**
     * Throws a {@see TransformationFailedException} if $value is not a numeric value.
     *
     * @param string $value
     */
    public function assertUuid(string $value): void
    {
        if (Uuid::isValid($value)) {
            return;
        }

        throw new TransformationFailedException($this->buildExceptionMessage($value, 'uuid'));
    }

    /**
     * Builds the expectation message.
     *
     * @param $value
     * @param string $expectedType
     *
     * @return string
     */
    private function buildExceptionMessage($value, string $expectedType): string
    {
        return \sprintf(
            'Expected "%s", got "%s".',
            $expectedType,
            \is_object($value) ? \get_class($value) : \gettype($value)
        );
    }
}
