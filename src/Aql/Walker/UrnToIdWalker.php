<?php declare(strict_types=1);

namespace MidnightCall\Utils\Aql\Walker;

use Fazland\ApiPlatformBundle\QueryLanguage\Expression\Literal\LiteralExpression;
use Fazland\ApiPlatformBundle\QueryLanguage\Walker\Doctrine\DqlWalker;

class UrnToIdWalker extends DqlWalker
{
    /**
     * {@inheritdoc}
     */
    public function walkLiteral(LiteralExpression $expression)
    {
        $value = \preg_replace('/\burn:sicuro:.+:([0-9a-z-]+)\b/', '$1', (string) $expression);

        return parent::walkLiteral(LiteralExpression::create($value));
    }
}
