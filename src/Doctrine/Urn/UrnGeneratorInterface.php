<?php declare(strict_types=1);

namespace MidnightCall\Utils\Doctrine\Urn;

interface UrnGeneratorInterface
{
    /**
     * Gets the urn identifier for this object.
     */
    public function getUrn(): string;
}
