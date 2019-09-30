<?php declare(strict_types=1);

namespace MidnightCall\Utils\Aql\Validation;

use Fazland\ApiPlatformBundle\QueryLanguage\Expression\ExpressionInterface;
use Fazland\ApiPlatformBundle\QueryLanguage\Expression\ValueExpression;
use Fazland\ApiPlatformBundle\QueryLanguage\Walker\Validation\ValidationWalker;

class ArrayLikeWalker extends ValidationWalker
{
    /**
     * {@inheritdoc}
     */
    public function walkComparison(string $operator, ValueExpression $expression)
    {
        $this->addViolation('Operation unavailable');
    }

    /**
     * {@inheritdoc}
     */
    public function walkEntry(string $key, ExpressionInterface $expression)
    {
        if ('length' === $key) {
            return;
        }

        $this->addViolation('"{{ key }}" is not a valid criteria entry. Must be "{{ allowed_keys }}"', [
            '{{ key }}' => $key,
            '{{ allowed_keys }}' => \implode('", "', ['length']),
        ]);
    }
}
