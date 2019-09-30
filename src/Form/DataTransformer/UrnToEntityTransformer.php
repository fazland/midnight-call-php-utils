<?php declare(strict_types=1);

namespace MidnightCall\Utils\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UrnToEntityTransformer extends AbstractOneWayDataTransformer
{
    use TypeAssertionTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var string
     */
    private $urnPattern;

    public function __construct(EntityManagerInterface $entityManager, string $entityClass, string $urnPattern)
    {
        $this->entityManager = $entityManager;
        $this->entityClass = $entityClass;
        $this->urnPattern = $urnPattern;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value): ?object
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if ($value instanceof $this->entityClass) {
            return $value;
        }

        $this->assertString($value);

        if (! \preg_match($this->urnPattern, $value, $matches)) {
            throw new TransformationFailedException(\sprintf(
                'The specified value is not a valid urn. The expected format is %s',
                $this->urnPattern
            ));
        }

        $this->assertUuid($matches['id']);

        $repository = $this->entityManager->getRepository($this->entityClass);
        $entity = $repository->find($matches['id']);

        if (null === $entity) {
            throw new TransformationFailedException(\sprintf(
                'Could not find the desired %s with ID %s',
                $this->entityClass,
                $matches['id']
            ));
        }

        return $entity;
    }
}
