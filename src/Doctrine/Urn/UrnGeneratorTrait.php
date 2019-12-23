<?php declare(strict_types=1);

namespace MidnightCall\Utils\Doctrine\Urn;

use Doctrine\Persistence\Proxy;
use ProxyManager\Proxy\ProxyInterface;

trait UrnGeneratorTrait
{
    /**
     * {@inheritdoc}
     */
    public function getUrn(): string
    {
        $className = static::class;
        if ($this instanceof Proxy || $this instanceof ProxyInterface) {
            $className = \get_parent_class($this);
        }

        if (null === $this->id) {
            throw new \InvalidArgumentException("This $className does not have an id. Persist it before generating the urn!");
        }

        $shortName = (new \ReflectionClass($className))->getShortName();
        $normalizedName = \strtolower(\preg_replace('~(?<=\\w)([A-Z])~', '-$1', $shortName));

        return "urn:%s:$normalizedName:$this->id";
    }
}
