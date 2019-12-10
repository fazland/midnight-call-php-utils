<?php declare(strict_types=1);

namespace MidnightCall\Utils\Tests\Serializer;

use Kcs\Serializer\Context;
use Kcs\Serializer\Type\Type;
use Kcs\Serializer\VisitorInterface;
use libphonenumber\PhoneNumber;
use MidnightCall\Utils\Serializer\PhoneNumberHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class PhoneNumberHandlerTest extends TestCase
{
    private PhoneNumberHandler $handler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->handler = new PhoneNumberHandler();
    }

    public function testSerializeShouldHandleNullValuesCorrectly(): void
    {
        self::assertNull($this->handler->serialize(
            $this->prophesize(VisitorInterface::class)->reveal(),
            null,
            Type::parse('phonenumber'),
            $this->prophesize(Context::class)->reveal()
        ));
    }

    public function testSerializeShouldWork(): void
    {
        $visitor = $this->prophesize(VisitorInterface::class);
        $visitor->visitString(Argument::type('string'), Argument::cetera())
            ->will(function ($args) {
                return $args[0];
            })
        ;

        $phoneNumber = $this->prophesize(PhoneNumber::class);
        $phoneNumber->getCountryCode()->willReturn('39');
        $phoneNumber->getNationalNumber()->willReturn('3391736002');

        self::assertEquals('+393391736002', $this->handler->serialize(
            $visitor->reveal(),
            $phoneNumber->reveal(),
            Type::parse('phonenumber'),
            $this->prophesize(Context::class)->reveal()
        ));
    }
}
