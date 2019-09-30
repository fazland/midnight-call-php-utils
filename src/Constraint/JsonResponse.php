<?php declare(strict_types=1);

namespace MidnightCall\Utils\Constraint;

use MidnightCall\Utils\Json;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\JsonMatchesErrorMessageProvider;
use Symfony\Component\HttpFoundation\Response;

final class JsonResponse extends Constraint
{
    /**
     * {@inheritdoc}
     */
    protected function matches($other): bool
    {
        if (! $other instanceof Response || ! \preg_match('/application\/json/', $other->headers->get('Content-Type'))) {
            return false;
        }

        $content = $other->getContent();

        if ('' === $content) {
            return false;
        }

        try {
            Json::decode($content);
        } catch (\JsonException $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function failureDescription($other): string
    {
        if (! $other instanceof Response) {
            return 'a response object';
        }

        if (! \preg_match('/application\/json/', $other->headers->get('Content-Type'))) {
            return 'content type';
        }

        $content = $other->getContent();

        if ('' === $content) {
            return 'an empty string is valid JSON';
        }

        \json_decode($content);
        $error = JsonMatchesErrorMessageProvider::determineJsonError(
            (string) \json_last_error()
        );

        return \sprintf(
            '%s is valid JSON response (%s)',
            $this->exporter->shortenedExport($other),
            $error
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return 'is valid JSON response';
    }
}
