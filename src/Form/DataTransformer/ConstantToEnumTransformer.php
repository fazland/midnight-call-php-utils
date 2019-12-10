<?php declare(strict_types=1);

namespace MidnightCall\Utils\Form\DataTransformer;

use MyCLabs\Enum\Enum;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ConstantToEnumTransformer extends AbstractOneWayDataTransformer
{
    use TypeAssertionTrait;

    private string $enumClass;

    public function __construct(string $enumClass)
    {
        $this->enumClass = $enumClass;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value): ?Enum
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if ($value instanceof $this->enumClass) {
            return $value;
        }

        $this->assertString($value);

        if (! $this->enumClass::isValid($value)) {
            throw new TransformationFailedException(\sprintf(
                'Invalid %s type. Got "%s".',
                $this->enumClass,
                $value
            ));
        }

        $target = $this->enumClass::search($value);

        return $this->enumClass::$target();
    }
}
