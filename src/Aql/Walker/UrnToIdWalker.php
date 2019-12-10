<?php declare(strict_types=1);

namespace MidnightCall\Utils\Aql\Walker;

use Doctrine\ORM\QueryBuilder;
use Fazland\ApiPlatformBundle\QueryLanguage\Expression\Literal\LiteralExpression;
use Fazland\ApiPlatformBundle\QueryLanguage\Walker\Doctrine\DqlWalker;

class UrnToIdWalker extends DqlWalker
{
    private string $urnPattern;

    public function __construct(QueryBuilder $queryBuilder, string $field, string $urnPattern, string $columnType = 'string')
    {
        parent::__construct($queryBuilder, $field, $columnType);

        $this->urnPattern = $urnPattern;
    }

    /**
     * {@inheritdoc}
     */
    public function walkLiteral(LiteralExpression $expression)
    {
        $value = \preg_replace($this->urnPattern, '$1', (string) $expression);

        return parent::walkLiteral(LiteralExpression::create($value));
    }
}
