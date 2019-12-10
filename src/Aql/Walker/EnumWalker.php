<?php declare(strict_types=1);

namespace MidnightCall\Utils\Aql\Walker;

use Doctrine\ORM\QueryBuilder;
use Fazland\ApiPlatformBundle\QueryLanguage\Expression\Literal\LiteralExpression;
use Fazland\ApiPlatformBundle\QueryLanguage\Walker\Doctrine\DqlWalker;

class EnumWalker extends DqlWalker
{
    private string $enumClass;

    public function __construct(QueryBuilder $queryBuilder, string $field, string $enumClass)
    {
        parent::__construct($queryBuilder, $field, $enumClass);

        $this->enumClass = $enumClass;
    }

    /**
     * {@inheritdoc}
     */
    public function walkLiteral(LiteralExpression $expression)
    {
        return new $this->enumClass((string) $expression);
    }
}
