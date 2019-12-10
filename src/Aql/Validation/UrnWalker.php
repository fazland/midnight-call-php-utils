<?php declare(strict_types=1);

namespace MidnightCall\Utils\Aql\Validation;

use Fazland\ApiPlatformBundle\QueryLanguage\Expression\Literal\LiteralExpression;
use Fazland\ApiPlatformBundle\QueryLanguage\Walker\Validation\ValidationWalker;

class UrnWalker extends ValidationWalker
{
    private string $urnPattern;

    private string $resourceType;

    public function __construct(string $urnPattern, string $resourceType)
    {
        $this->urnPattern = $urnPattern;
        $this->resourceType = $resourceType;
    }

    /**
     * {@inheritdoc}
     */
    public function walkLiteral(LiteralExpression $expression)
    {
        if (! \preg_match($this->urnPattern, (string) $expression)) {
            $this->addViolation('"{{ value }}" is not a valid {{ resource_type }} urn. The urn format is "{{ urn_format }}"', [
                '{{ value }}' => (string) $expression,
                '{{ resource_type }}' => $this->resourceType,
                '{{ urn_format }}' => $this->urnPattern,
            ]);
        }
    }
}
