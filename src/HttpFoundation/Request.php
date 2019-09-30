<?php declare(strict_types=1);

namespace MidnightCall\Utils\HttpFoundation;

use Symfony\Component\HttpFoundation\Request as BaseRequest;

class Request extends BaseRequest
{
    public const IMAGE_FORMATS = [
        'png' => ['image/png', 'image/apng'],
        'jpeg' => ['image/jpeg'],
        'svg' => ['image/svg+xml'],
        'gif' => ['image/gif'],
    ];

    /**
     * {@inheritdoc}
     */
    protected static function initializeFormats(): void
    {
        parent::initializeFormats();

        static::$formats += self::IMAGE_FORMATS;
    }
}
