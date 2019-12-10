<?php declare(strict_types=1);

namespace MidnightCall\Utils\Tests\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Fazland\DoctrineExtra\ORM\EntityRepository;
use MidnightCall\Utils\Form\DataTransformer\UrnToEntityTransformer;
use MidnightCall\Utils\Tests\Fixtures\Entity\FooEntity;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UrnToEntityTransformerTest extends TestCase
{
    /**
     * @var EntityManagerInterface|ObjectProphecy
     */
    private object $entityManager;

    /**
     * @var EntityRepository|ObjectProphecy
     */
    private object $entityRepository;

    private UrnToEntityTransformer $transformer;

    private string $entityClass;

    private string $urnPattern;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->entityClass = FooEntity::class;
        $this->urnPattern = '/^urn:midnight-call:entity:(?P<id>[0-9a-z-]+)$/';

        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->entityRepository = $this->prophesize(EntityRepository::class);
        $this->entityManager->getRepository($this->entityClass)->willReturn($this->entityRepository);

        $this->transformer = new UrnToEntityTransformer($this->entityManager->reveal(), $this->entityClass, $this->urnPattern);
    }

    public function emptyValues(): iterable
    {
        yield [''];
        yield [null];
    }

    /**
     * @dataProvider emptyValues
     */
    public function testReverseTransformShouldReturnNullOnEmptyValues($value): void
    {
        $this->entityRepository->find(Argument::cetera())->shouldNotBeCalled();

        self::assertNull($this->transformer->reverseTransform($value));
    }

    public function testReverseTransformShouldNotActIfValueIsAnInstanceOfEntityClass(): void
    {
        $entity = $this->prophesize($this->entityClass);

        self::assertEquals($this->transformer->reverseTransform($entity->reveal()), $entity->reveal());
    }

    public function nonStringValues(): iterable
    {
        yield [0.23];
        yield [47];
        yield [['foobar']];
        yield [new \stdClass()];
    }

    /**
     * @dataProvider nonStringValues
     */
    public function testReverseTransformShouldThrowOnNonStringValues($value): void
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer->reverseTransform($value);
    }

    public function provideInvalidStrings(): iterable
    {
        yield ['urn:midnight-call:entity:not_an_uuid'];
        yield ['urn:midnight-call:entity:de23c3a7-2986-4204-8ad1-ab5cad37bd4']; // UUID without the last char.
    }

    /**
     * @dataProvider provideInvalidStrings
     */
    public function testReverseTransformShouldThrowOnInvalidStringValues(string $value): void
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer->reverseTransform($value);
    }

    public function testReverseTransformShouldThrowIfEntityWasNotFound(): void
    {
        $this->expectException(TransformationFailedException::class);

        $value = 'urn:midnight-call:entity:'.(string) Uuid::uuid4();
        $criteriaValue = \str_replace('urn:midnight-call:entity:', '', $value);
        $this->entityRepository
            ->find($criteriaValue)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->transformer->reverseTransform($value);
    }

    public function testReverseTransformShouldWork(): void
    {
        $entity = $this->prophesize($this->entityClass);

        $value = 'urn:midnight-call:entity:'.(string) Uuid::uuid4();
        $criteriaValue = \str_replace('urn:midnight-call:entity:', '', $value);
        $this->entityRepository
            ->find(\mb_strtolower($criteriaValue))
            ->shouldBeCalled()
            ->willReturn($entity)
        ;

        self::assertEquals($this->transformer->reverseTransform($value), $entity->reveal());
    }
}
