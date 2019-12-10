<?php declare(strict_types=1);

namespace MidnightCall\Utils\Statics;

/**
 * Resolves a filename/path to a fully-qualified, exposable URI.
 */
interface ResolverInterface
{
    /**
     * Resolves a path to a fully-qualified URI.
     * Should throw an UnresolvablePathException if path is not resolvable
     * to a valid URI.
     */
    public function resolve(string $path): string;
}
