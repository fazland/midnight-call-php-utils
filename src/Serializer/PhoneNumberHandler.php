<?php declare(strict_types=1);

namespace MidnightCall\Utils\Serializer;

use Kcs\Serializer\Context;
use Kcs\Serializer\Direction;
use Kcs\Serializer\Handler\SubscribingHandlerInterface;
use Kcs\Serializer\Type\Type;
use Kcs\Serializer\VisitorInterface;
use libphonenumber\PhoneNumber;

class PhoneNumberHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods(): iterable
    {
        yield [
            'type' => 'phonenumber',
            'direction' => Direction::DIRECTION_SERIALIZATION,
            'method' => 'serialize',
        ];
    }

    public function serialize(VisitorInterface $visitor, ?PhoneNumber $data, Type $type, Context $context)
    {
        if (null === $data) {
            return null;
        }

        /*
         * Serialized as '+[countrycode][nationalnumber]' to be coherent with the input data
         * (form validation evaluates '3394547339' as invalid, and '+393394547339' as valid). Change if needed.
         */
        return $visitor->visitString('+'.$data->getCountryCode().$data->getNationalNumber(), $type, $context);
    }
}
