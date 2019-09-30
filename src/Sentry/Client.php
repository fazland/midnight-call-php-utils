<?php declare(strict_types=1);

namespace MidnightCall\Utils\Sentry;

use Sentry\SentryBundle\SentrySymfonyClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class Client extends SentrySymfonyClient
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    protected function get_http_data(): array
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (! $currentRequest instanceof Request) {
            return parent::get_http_data();
        }

        $cookies = $currentRequest->cookies->all();
        $headers = \array_map(static function (array $values): string {
            return \implode(', ', $values);
        }, $currentRequest->headers->all());

        $result = [
            'method' => $currentRequest->getMethod(),
            'url' => $currentRequest->getUri(),
            'query_string' => $currentRequest->getQueryString(),
        ];

        // don't set this as an empty array as PHP will treat it as a numeric array
        // instead of a mapping which goes against the defined Sentry spec
        if ($currentRequest->request->count() > 0) {
            $result['data'] = $currentRequest->request->all();
        }

        if (! empty($cookies)) {
            $result['cookies'] = $cookies;
        }
        if (! empty($headers)) {
            $result['headers'] = $headers;
        }

        return [
            'request' => $result,
        ];
    }
}
