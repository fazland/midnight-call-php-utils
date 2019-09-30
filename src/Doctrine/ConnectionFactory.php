<?php declare(strict_types=1);

namespace MidnightCall\Utils\Doctrine;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory as BaseFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Fazland\DoctrineExtra\ORM\Type\PhpEnumType;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final class ConnectionFactory extends BaseFactory implements ServiceSubscriberInterface
{
    /**
     * Indicates whether custom types have been already registered.
     *
     * @var bool
     */
    private $initialized = false;

    /**
     * The enum classes to be registered as doctrine types.
     *
     * @var string[]
     */
    private $enums = [];

    /**
     * @var BaseFactory
     */
    private $factory;

    public function __construct(BaseFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function createConnection(
        array $params,
        Configuration $config = null,
        EventManager $eventManager = null,
        array $mappingTypes = []
    ): Connection {
        if (! $this->initialized) {
            $this->initialize();
        }

        [$params['host'], $params['port']] = $this->resolveSrv($params['host'] ?? 'localhost', $params['port'] ?? null);
        if (isset($params['url'])) {
            $urlParams = \parse_url($params['url']);
            [$urlParams['host'], $urlParams['port']] = $this->resolveSrv($urlParams['host'] ?? '', $urlParams['port'] ?? null);

            $params['url'] = $this->buildUrl($urlParams);
        }

        $connection = $this->factory->createConnection($params, $config, $eventManager, $mappingTypes);
        $platform = $connection->getDatabasePlatform();

        foreach ($this->enums as $enum) {
            $platform->markDoctrineTypeCommented(Type::getType($enum));
            $platform->markDoctrineTypeCommented(Type::getType("array<$enum>"));
        }

        return $connection;
    }

    /**
     * @param string[] $enums
     */
    public function setEnums(array $enums): void
    {
        $this->enums = \array_values($enums);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            CacheItemPoolInterface::class,
        ];
    }

    private function initialize(): void
    {
        foreach ($this->enums as $enum) {
            if (Type::hasType($enum)) {
                continue;
            }

            PhpEnumType::registerEnumType($enum);
        }

        $this->initialized = true;
    }

    private function resolveSrv(string $host, ?int $port): array
    {
        if (0 !== \strpos($host, 'srv:')) {
            return [$host, $port];
        }

        $srvRecord = \dns_get_record(\substr($host, 4), DNS_SRV);
        if (false === $srvRecord || 0 === \count($srvRecord)) {
            return [$host, $port];
        }

        return [$srvRecord[0]['target'], $srvRecord[0]['port']];
    }

    private function buildUrl(array $parts): string
    {
        return
            (isset($parts['scheme']) ? $parts['scheme'].':' : '').
            ((isset($parts['user']) || isset($parts['host'])) ? '//' : '').
            ($parts['user'] ?? '').
            (isset($parts['pass']) ? ':'.$parts['pass'] : '').
            (isset($parts['user']) ? '@' : '').
            ($parts['host'] ?? '').
            (isset($parts['port']) ? ':'.$parts['port'] : '').
            ($parts['path'] ?? '').
            (isset($parts['query']) ? '?'.$parts['query'] : '').
            (isset($parts['fragment']) ? '#'.$parts['fragment'] : '')
        ;
    }
}
