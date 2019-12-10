<?php declare(strict_types=1);

namespace MidnightCall\Utils\Tests\Serializer;

use Kcs\Serializer\Context;
use Kcs\Serializer\Type\Type;
use Kcs\Serializer\VisitorInterface;
use MidnightCall\Utils\Doctrine\Urn\UrnGeneratorInterface;
use MidnightCall\Utils\Serializer\UrnGeneratorHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class UrnGeneratorHandlerTest extends TestCase
{
    private UrnGeneratorHandler $handler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->handler = new UrnGeneratorHandler();
    }

    public function testSerializeShouldHandleNullValuesCorrectly(): void
    {
        self::assertNull($this->handler->serialize(
            $this->prophesize(VisitorInterface::class)->reveal(),
            null,
            Type::parse('urn'),
            $this->prophesize(Context::class)->reveal()
        ));
    }

    public function testSerializeShouldWork(): void
    {
        $visitor = $this->prophesize(VisitorInterface::class);
        $visitor->visitString(Argument::type('string'), Argument::cetera())->will(function ($args) {
            return $args[0];
        });

        $urnGenerator = $this->prophesize(UrnGeneratorInterface::class);
        $urnGenerator->getUrn()->willReturn('urn:sicuro:foobar:12');

        self::assertEquals('urn:sicuro:foobar:12', $this->handler->serialize(
            $visitor->reveal(),
            $urnGenerator->reveal(),
            Type::parse('urn'),
            $this->prophesize(Context::class)->reveal()
        ));
    }
}
