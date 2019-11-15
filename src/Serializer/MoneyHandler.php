<?php declare(strict_types=1);

namespace MidnightCall\Utils\Serializer;

use Kcs\Serializer\Context;
use Kcs\Serializer\Direction;
use Kcs\Serializer\Handler\SubscribingHandlerInterface;
use Kcs\Serializer\Type\Type;
use Kcs\Serializer\VisitorInterface;
use MidnightCall\Utils\Money\PreciseMoney;
use Money\Currency;
use Money\Money;

class MoneyHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods(): iterable
    {
        yield [
            'type' => Money::class,
            'direction' => Direction::DIRECTION_SERIALIZATION,
            'method' => 'serialize',
        ];

        yield [
            'type' => PreciseMoney::class,
            'direction' => Direction::DIRECTION_SERIALIZATION,
            'method' => 'serialize',
        ];

        yield [
            'type' => Currency::class,
            'direction' => Direction::DIRECTION_SERIALIZATION,
            'method' => 'serializeCurrency',
        ];

        yield [
            'type' => Money::class,
            'direction' => Direction::DIRECTION_DESERIALIZATION,
            'method' => 'deserialize',
        ];

        yield [
            'type' => PreciseMoney::class,
            'direction' => Direction::DIRECTION_DESERIALIZATION,
            'method' => 'deserialize',
        ];

        yield [
            'type' => Currency::class,
            'direction' => Direction::DIRECTION_DESERIALIZATION,
            'method' => 'deserializeCurrency',
        ];
    }

    public function serialize(VisitorInterface $visitor, ?Money $data, Type $type, Context $context)
    {
        if (null === $data) {
            return null;
        }

        return $visitor->visitHash([
            'amount' => $data->getAmount(),
            'currency' => (string) $data->getCurrency(),
        ], $type, $context);
    }

    public function serializeCurrency(VisitorInterface $visitor, ?Currency $data, Type $type, Context $context)
    {
        if (null === $data) {
            return null;
        }

        return $visitor->visitString((string) $data, $type, $context);
    }

    public function deserialize(VisitorInterface $visitor, ?array $data, Type $type, Context $context)
    {
        if (null === $data) {
            return null;
        }

        return new Money($data['amount'], new Currency($data['currency']));
    }

    public function deserializeCurrency(VisitorInterface $visitor, ?string $data, Type $type, Context $context)
    {
        if (null === $data) {
            return null;
        }

        return new Currency($data);
    }
}
