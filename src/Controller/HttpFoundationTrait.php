<?php declare(strict_types=1);

namespace MidnightCall\Utils\Controller;

use MidnightCall\Utils\Constraint\JsonResponse;
use MidnightCall\Utils\Json;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\IsFalse;
use PHPUnit\Framework\Constraint\IsTrue;
use PHPUnit\Framework\Constraint\IsType;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @method static void ensureKernelShutdown()
 */
trait HttpFoundationTrait
{
    /**
     * The client used in last request.
     *
     * @var KernelBrowser
     */
    private static $client;

    /**
     * Creates a Client.
     *
     * Do not specify a return type.
     * This method is inherited from {@see \Symfony\Bundle\FrameworkBundle\Test\WebTestCase::createClient()}.
     *
     * @param array $options An array of options to pass to the createKernel class
     * @param array $server  An array of server parameters
     *
     * @return KernelBrowser A KernelBrowser instance
     */
    abstract protected static function createClient(array $options = [], array $server = []);

    /**
     * Retrieves a valid access token.
     *
     * NOTE: This uses data from fixtures
     *
     * @see ClientLoader
     *
     * @param string      $clientId
     * @param string      $clientSecret
     * @param string      $grantType
     * @param array       $additionalParams
     * @param string|null $refreshToken
     *
     * @return string
     */
    public function getJwt(
        string $clientId,
        string $clientSecret,
        string $grantType,
        array $additionalParams = [],
        ?string &$refreshToken = null
    ): string {
        $requestData = \array_merge([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => $grantType,
        ], $additionalParams);

        $response = self::post('/token', $requestData);
        if (! $response->isOk()) {
            throw new AssertionFailedError(\sprintf(
                'Error while getting an access token: %s',
                $response->getContent()
            ));
        }

        try {
            $refreshToken = self::readJsonResponseProperty($response, 'refresh_token');
        } catch (AssertionFailedError $e) {
            // Do nothing.
        }

        return self::readJsonResponseProperty($response, 'access_token');
    }

    /**
     * Gets the accept header with the specified version.
     *
     * @param string $version
     *
     * @return string[]
     */
    private function getAcceptHeader(string $version): array
    {
        return ['Accept' => "application/json; version=$version"];
    }

    /**
     * Gets the merge patch header.
     * If version is specified, also the accept header with that version is returned.
     *
     * @param string|null $version
     *
     * @return string[]
     */
    private function getMergePatchHeader(string $version = null): array
    {
        $mergePatchHeader = ['Content-Type' => 'application/merge-patch+json'];
        if (null === $version) {
            return $mergePatchHeader;
        }

        return \array_merge($mergePatchHeader, $this->getAcceptHeader($version));
    }

    private static function get(
        string $url,
        string $accessToken = null,
        array $additionalHeaders = []
    ): Response {
        return self::request($url, 'GET', null, $accessToken, $additionalHeaders);
    }

    private static function post(
        string $url,
        array $requestData = null,
        string $accessToken = null,
        array $additionalHeaders = []
    ): Response {
        return self::request($url, 'POST', $requestData, $accessToken, $additionalHeaders);
    }

    private static function put(
        string $url,
        array $requestData = null,
        string $accessToken = null,
        array $additionalHeaders = []
    ): Response {
        return self::request($url, 'PUT', $requestData, $accessToken, $additionalHeaders);
    }

    private static function patch(
        string $url,
        array $requestData = null,
        string $accessToken = null,
        array $additionalHeaders = []
    ): Response {
        return self::request($url, 'PATCH', $requestData, $accessToken, $additionalHeaders);
    }

    private static function delete(
        string $url,
        string $accessToken = null,
        array $additionalHeaders = []
    ): Response {
        return self::request($url, 'DELETE', null, $accessToken, $additionalHeaders);
    }

    /**
     * Performs a request.
     *
     * @param string      $url
     * @param string      $method
     * @param array       $requestData
     * @param string|null $accessToken
     * @param array       $additionalHeaders
     *
     * @return Response
     */
    private static function request(
        string $url,
        string $method,
        array $requestData = null,
        string $accessToken = null,
        array $additionalHeaders = []
    ): Response {
        $contentHeaders = ['content-length' => 'CONTENT_LENGTH', 'content-md5' => 'CONTENT_MD5', 'content-type' => 'CONTENT_TYPE'];
        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ];

        foreach ($additionalHeaders as $header => $value) {
            $header = \strtolower($header);
            if (isset($contentHeaders[$header])) {
                $headers[$contentHeaders[$header]] = $value;
            } elseif ('PHP_AUTH_USER' === $header || 'PHP_AUTH_PW' === $header) {
                $headers[$header] = $value;
            } else {
                $headers['HTTP_'.\str_replace('-', '_', \strtoupper($header))] = $value;
            }
        }

        if (null !== $accessToken) {
            $headers['HTTP_AUTHORIZATION'] = "Bearer $accessToken";
        }

        self::ensureKernelShutdown();

        self::$client = static::createClient();
        self::$client->request($method, $url, [], [], $headers, null !== $requestData ? \json_encode($requestData) : null);

        return self::$client->getResponse();
    }

    /**
     * Asserts $response is a JSON response.
     *
     * @param Response $response
     */
    public static function assertJsonResponse(Response $response): void
    {
        self::assertThat($response, new JsonResponse(), 'Not a JSON response');
    }

    /**
     * Asserts the array of property names are in JSON response.
     *
     * @param Response $response
     * @param array    $expectedProperties
     */
    public static function assertJsonResponsePropertiesExist(Response $response, array $expectedProperties): void
    {
        $accessor = self::getPropertyAccessor();
        $data = Json::decode($response->getContent(), false);

        $missingProperty = null;
        foreach ($expectedProperties as $propertyPath) {
            if (! $accessor->isReadable($data, $propertyPath)) {
                $missingProperty = $propertyPath;

                break;
            }
        }

        self::assertThat(null === $missingProperty, new IsTrue(), \sprintf(
            'Property "%s" does not exist',
            $missingProperty
        ));
    }

    /**
     * Asserts the specific propertyPath is in the JSON response.
     *
     * @param Response $response
     * @param string   $propertyPath
     */
    public static function assertJsonResponsePropertyExists(Response $response, string $propertyPath): void
    {
        $accessor = self::getPropertyAccessor();
        $data = Json::decode($response->getContent(), false);

        self::assertThat($accessor->isReadable($data, $propertyPath), new IsTrue(), 'Property "'.$propertyPath.'" exists');
    }

    /**
     * Asserts the given property path does *not* exist.
     *
     * @param Response $response
     * @param string   $propertyPath
     */
    public static function assertJsonResponsePropertyDoesNotExist(Response $response, string $propertyPath): void
    {
        $accessor = self::getPropertyAccessor();
        $data = Json::decode($response->getContent(), false);

        self::assertThat($accessor->isReadable($data, $propertyPath), new IsFalse(), \sprintf('Property "%s" exists, but it should not', $propertyPath));
    }

    /**
     * Asserts the response JSON property equals the given value.
     *
     * @param Response $response
     * @param string   $propertyPath
     * @param mixed    $expectedValue
     */
    public static function assertJsonResponsePropertyEquals(Response $response, string $propertyPath, $expectedValue): void
    {
        $actual = self::readJsonResponseProperty($response, $propertyPath);

        self::assertThat(
            $actual,
            new IsEqual($expectedValue),
            \sprintf('Property "%s": Expected %s but response was %s',
                $propertyPath,
                \var_export($expectedValue, true),
                \var_export($actual, true)
            )
        );
    }

    /**
     * Asserts the response property is an array.
     *
     * @param Response $response
     * @param string   $propertyPath
     *
     * @throws \Throwable
     */
    private static function assertJsonResponsePropertyIsArray(Response $response, string $propertyPath): void
    {
        self::assertJsonResponsePropertyIsType($response, $propertyPath, 'array');
    }

    /**
     * Checks it the internal value of a JSON property is of the given type. It uses standard PhpUnit\Assert type-checking.
     *
     * Available values are:
     * 'array'    => true,
     * 'boolean'  => true,
     * 'bool'     => true,
     * 'double'   => true,
     * 'float'    => true,
     * 'integer'  => true,
     * 'int'      => true,
     * 'null'     => true,
     * 'numeric'  => true,
     * 'object'   => true,
     * 'real'     => true,
     * 'resource' => true,
     * 'string'   => true,
     * 'scalar'   => true,
     * 'callable' => true
     *
     * @param Response $response
     * @param string   $propertyPath
     * @param string   $type
     */
    private static function assertJsonResponsePropertyIsType(Response $response, string $propertyPath, string $type): void
    {
        static::assertThat(
            self::readJsonResponseProperty($response, $propertyPath),
            new IsType($type)
        );
    }

    /**
     * Asserts the given response property (probably an array) has the expected "count".
     *
     * @param Response $response
     * @param string   $propertyPath
     * @param int      $expectedCount
     *
     * @throws \Throwable
     */
    private static function assertJsonResponsePropertyCount(Response $response, string $propertyPath, int $expectedCount): void
    {
        self::assertCount($expectedCount, self::readJsonResponseProperty($response, $propertyPath));
    }

    /**
     * Asserts the specific response property contains the given value.
     *
     * e.g. "Hello world!" contains "world"
     *
     * @param Response $response
     * @param string   $propertyPath
     * @param mixed    $expectedValue
     */
    public static function assertJsonResponsePropertyContains(Response $response, string $propertyPath, $expectedValue): void
    {
        $actualPropertyValue = self::readJsonResponseProperty($response, $propertyPath);

        self::assertContains(
            $expectedValue,
            $actualPropertyValue,
            \sprintf(
                'Property "%s": Expected to contain "%s" but response was "%s"',
                $propertyPath,
                \var_export($expectedValue, true),
                \var_export($actualPropertyValue, true)
            )
        );
    }

    /**
     * Decodes a Response content into a JSON and reads its properties, given a propertyPath.
     * It uses a PropertyAccessor to access the fields, so it accepts propertyPath values formatted as.
     *
     * 'children[0].firstName'
     * 'children.son.nephew.fieldName'
     *
     * @see http://symfony.com/doc/current/components/property_access.html
     *
     * This will throw Exception if the value does not exist
     *
     * @param Response $response
     * @param string   $propertyPath e.g. firstName, battles[0].programmer.username
     *
     * @return mixed
     */
    private static function readJsonResponseProperty(Response $response, string $propertyPath)
    {
        $accessor = self::getPropertyAccessor();
        $data = Json::decode($response->getContent(), false);

        try {
            return $accessor->getValue($data, $propertyPath);
        } catch (AccessException $e) {
            $values = \is_array($data) ? $data : \get_object_vars($data);

            throw new AssertionFailedError(\sprintf(
                'Error reading property "%s" from available keys (%s)',
                $propertyPath,
                \implode(', ', \array_keys($values))
            ), 0, $e);
        }
    }

    public static function assertResponseIs(int $expectedCode, Response $response): void
    {
        self::assertThat(
            $response->getStatusCode(),
            new IsEqual($expectedCode),
            self::buildExpectedStatusCodeMessage($expectedCode, $response->getStatusCode())
        );
    }

    public static function assertResponseIsOk(Response $response): void
    {
        self::assertResponseIs(Response::HTTP_OK, $response);
    }

    public static function assertResponseIsCreated(Response $response): void
    {
        self::assertResponseIs(Response::HTTP_CREATED, $response);
    }

    public static function assertResponseIsAccepted(Response $response): void
    {
        self::assertResponseIs(Response::HTTP_ACCEPTED, $response);
    }

    public static function assertResponseIsNoContent(Response $response): void
    {
        self::assertResponseIs(Response::HTTP_NO_CONTENT, $response);
    }

    public static function assertResponseIsFound(Response $response): void
    {
        self::assertResponseIs(Response::HTTP_FOUND, $response);
    }

    public static function assertResponseIsBadRequest(Response $response): void
    {
        self::assertResponseIs(Response::HTTP_BAD_REQUEST, $response);
    }

    public static function assertResponseIsUnauthorized(Response $response): void
    {
        self::assertResponseIs(Response::HTTP_UNAUTHORIZED, $response);
    }

    public static function assertResponseIsForbidden(Response $response): void
    {
        self::assertResponseIs(Response::HTTP_FORBIDDEN, $response);
    }

    public static function assertResponseIsNotFound(Response $response): void
    {
        self::assertResponseIs(Response::HTTP_NOT_FOUND, $response);
    }

    public static function assertResponseIsNotSuccessful(Response $response): void
    {
        self::assertThat(
            $response->isSuccessful(),
            new IsFalse(),
            self::buildExpectedStatusCodeMessage('unsuccessful', $response->getStatusCode(), '< 200 || >= 300')
        );
    }

    public static function assertResponseIsUnprocessableEntity(Response $response): void
    {
        self::assertResponseIs(Response::HTTP_UNPROCESSABLE_ENTITY, $response);
    }

    public static function assertResponseIsPaymentRequired(Response $response): void
    {
        self::assertResponseIs(Response::HTTP_PAYMENT_REQUIRED, $response);
    }

    public static function assertResponseIsMethodNotAllowed(Response $response): void
    {
        self::assertResponseIs(Response::HTTP_METHOD_NOT_ALLOWED, $response);
    }

    public static function assertResponseIsPreconditionFailed(Response $response): void
    {
        self::assertResponseIs(Response::HTTP_PRECONDITION_FAILED, $response);
    }

    public static function assertResponseIsNotImplemented(Response $response): void
    {
        self::assertResponseIs(Response::HTTP_NOT_IMPLEMENTED, $response);
    }

    /**
     * Returns a valid property accessor.
     *
     * @return PropertyAccessorInterface
     */
    private static function getPropertyAccessor(): PropertyAccessorInterface
    {
        static $accessor = null;
        if (null === $accessor) {
            $accessor = PropertyAccess::createPropertyAccessor();
        }

        return $accessor;
    }

    /**
     * Builds the expectation message used in response assertions.
     *
     * @param int|string  $expected
     * @param int         $statusCode
     * @param string|null $interval
     *
     * @return string
     */
    private static function buildExpectedStatusCodeMessage(
        $expected,
        int $statusCode,
        string $interval = null
    ): string {
        return \sprintf(
            'Expected %s response (%s). Got Status code %d',
            \is_int($expected) ? Response::$statusTexts[$expected] : $expected,
            $interval ?? $expected,
            $statusCode
        );
    }
}
