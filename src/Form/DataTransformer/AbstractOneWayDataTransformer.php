<?php declare(strict_types=1);

namespace MidnightCall\Utils\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

abstract class AbstractOneWayDataTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value): void
    {
        // do nothing.
    }
}
