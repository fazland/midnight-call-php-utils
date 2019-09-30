<?php declare(strict_types=1);

namespace MidnightCall\Utils\Tests\Doctrine;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory as BaseFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Fazland\DoctrineExtra\ORM\Type\PhpEnumType;
use MidnightCall\Utils\Doctrine\ConnectionFactory;
use MidnightCall\Utils\Tests\Fixtures\Enum\FooBarEnum;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class ConnectionFactoryTest extends TestCase
{
    /**
     * @var ConnectionFactory
     */
    private $factory;

    /**
     * @var BaseFactory|ObjectProphecy
     */
    private $baseFactory;

    /**
     * @var Connection|ObjectProphecy
     */
    private $connection;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->baseFactory = $this->prophesize(BaseFactory::class);
        $this->connection = $this->prophesize(Connection::class);
        $this->baseFactory->createConnection(Argument::cetera())
            ->willReturn($this->connection)
        ;

        $this->factory = new ConnectionFactory($this->baseFactory->reveal());
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $enumClass = FooBarEnum::class;
        if (Type::hasType($enumClass)) {
            Type::overrideType($enumClass, null);
        }

        if (Type::hasType("array<$enumClass>")) {
            Type::overrideType("array<$enumClass>", null);
        }

        $reflection = new \ReflectionClass(Type::class);
        $property = $reflection->getProperty('_typesMap');
        $property->setAccessible(true);

        $value = $property->getValue(null);
        unset($value[$enumClass], $value["array<$enumClass>"]);

        $property->setValue(null, $value);
    }

    public function testCreateConnectionShouldRegisterEnumTypes(): void
    {
        self::assertFalse(Type::hasType(FooBarEnum::class));

        $this->factory->setEnums([FooBarEnum::class]);

        $platform = $this->prophesize(AbstractPlatform::class);
        $this->connection->getDatabasePlatform()->willReturn($platform);
        $platform->markDoctrineTypeCommented(Argument::any())->willReturn();

        $this->factory->createConnection([]);

        self::assertTrue(Type::hasType(FooBarEnum::class));
        self::assertInstanceOf(PhpEnumType::class, Type::getType(FooBarEnum::class));

        $platform->markDoctrineTypeCommented(Type::getType(FooBarEnum::class))->shouldHaveBeenCalled();
    }
}
