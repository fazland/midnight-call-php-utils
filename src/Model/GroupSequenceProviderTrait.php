<?php declare(strict_types=1);

namespace MidnightCall\Utils\Model;

use Symfony\Component\Validator\Constraint;

trait GroupSequenceProviderTrait
{
    /**
     * Gets the default validation groups for the current class.
     *
     * @return array
     */
    public function getDefaultValidationGroups(): array
    {
        $reflectionClass = new \ReflectionClass($this);

        return [Constraint::DEFAULT_GROUP, $reflectionClass->getShortName()];
    }
}
