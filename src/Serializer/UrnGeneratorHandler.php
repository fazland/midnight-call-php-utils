<?php declare(strict_types=1);

namespace MidnightCall\Utils\Serializer;

use Kcs\Serializer\Context;
use Kcs\Serializer\Direction;
use Kcs\Serializer\Handler\SubscribingHandlerInterface;
use Kcs\Serializer\Type\Type;
use Kcs\Serializer\VisitorInterface;
use MidnightCall\Utils\Doctrine\Urn\UrnGeneratorInterface;

class UrnGeneratorHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods(): iterable
    {
        yield [
            'type' => 'urn',
            'direction' => Direction::DIRECTION_SERIALIZATION,
            'method' => 'serialize',
        ];
    }

    public function serialize(VisitorInterface $visitor, ?UrnGeneratorInterface $data, Type $type, Context $context)
    {
        if (null === $data) {
            return null;
        }

        return $visitor->visitString($data->getUrn(), $type, $context);
    }
}
