<?php declare(strict_types=1);

namespace MidnightCall\Utils\Aql\Validation;

use Fazland\ApiPlatformBundle\QueryLanguage\Expression\ValueExpression;
use Fazland\ApiPlatformBundle\QueryLanguage\Walker\Validation\ValidationWalker;

class UuidWalker extends ValidationWalker
{
    /**
     * {@inheritdoc}
     */
    public function walkComparison(string $operator, ValueExpression $expression)
    {
        if ('=' !== $operator && 'like' !== $operator) {
            $this->addViolation('"{{ expression }}" operator is not supported. $eq and $like are the only supported operators.', [
                '{{ expression }}' => $operator,
            ]);
        }

        parent::walkComparison($operator, $expression);
    }
}
