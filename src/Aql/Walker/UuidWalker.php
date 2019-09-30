<?php declare(strict_types=1);

namespace MidnightCall\Utils\Aql\Walker;

use Doctrine\ORM\QueryBuilder;
use Fazland\ApiPlatformBundle\QueryLanguage\Walker\Doctrine\DqlWalker;

class UuidWalker extends DqlWalker
{
    public function __construct(QueryBuilder $queryBuilder, string $field)
    {
        parent::__construct($queryBuilder, $field);
    }
}
