<?php declare(strict_types=1);

namespace MidnightCall\Utils\Fixtures;

use Fazland\ApiPlatformBundle\Form\DataTransformer\Base64ToUploadedFileTransformer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\File;

trait FixtureTrait
{
    /**
     * Sets the id property on the target object.
     *
     * @param UuidInterface|string $id
     */
    private function setId(object $target, $id, ?string $targetScope = null): void
    {
        if (! $id instanceof UuidInterface) {
            $id = Uuid::fromString($id);
        }

        $this->setProperties($target, ['id' => $id], $targetScope);
    }

    /**
     * Sets the properties on the target object.
     */
    private function setProperties(object $target, array $properties, ?string $targetScope = null): void
    {
        $setter = function (array $properties): void {
            foreach ($properties as $propertyName => $propertyValue) {
                $this->$propertyName = $propertyValue;
            }
        };

        ($setter->bindTo($target, $targetScope ?? \get_class($target)))($properties);
    }

    /**
     * Gets a lorem ipsum description.
     */
    private function getDescription(): string
    {
        return <<<HTML
<h5>
    Lorem ipsum dolor sit amet
</h5>
<p>
    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum
</p>
<h5>
    Lorem ipsum dolor sit amet
</h5>
<p>
    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum
</p>
HTML;
    }

    /**
     * Gets the fixture gif image file path.
     */
    private function getGifImagePath(): string
    {
        return __DIR__.'/../../tests/Fixtures/Assets/Images/test.gif';
    }

    /**
     * Gets the fixture png image file path.
     */
    private function getPngImagePath(): string
    {
        return __DIR__.'/../../tests/Fixtures/Assets/Images/test.png';
    }

    /**
     * Gets the fixture gif image content.
     */
    private function getGifImage(): string
    {
        return \file_get_contents($this->getGifImagePath());
    }

    /**
     * Gets the fixture png image content.
     */
    private function getPngImage(): string
    {
        return \file_get_contents($this->getPngImagePath());
    }

    /**
     * Gets the fixture gif image as data uri.
     */
    private function getGifDataUri(): string
    {
        return $this->getImageDataUri($this->getGifImagePath());
    }

    /**
     * Gets the fixture png image as data uri.
     */
    private function getPngDataUri(): string
    {
        return $this->getImageDataUri($this->getPngImagePath());
    }

    /**
     * Gets the image as data uri.
     */
    private function getImageDataUri(string $filePath): string
    {
        static $base64Transformer = null;
        if (null === $base64Transformer) {
            $base64Transformer = new Base64ToUploadedFileTransformer();
        }

        return $base64Transformer->transform(new File($filePath));
    }

    /**
     * Gets a random string generated from a Uuid.
     */
    private function getRandomString(): string
    {
        return \str_replace('-', '', \strtoupper(Uuid::uuid4()));
    }
}
