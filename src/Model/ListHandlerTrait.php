<?php declare(strict_types=1);

namespace MidnightCall\Utils\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Fazland\ApiPlatformBundle\QueryLanguage\Processor\Doctrine\ORM\Processor;
use Fazland\DoctrineExtra\ObjectIteratorInterface;
use Fazland\DtoManagementBundle\InterfaceResolver\ResolverInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

trait ListHandlerTrait
{
    protected FormFactoryInterface $formFactory;

    protected EntityManagerInterface $entityManager;

    protected ResolverInterface $resolver;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        ResolverInterface $resolver
    ) {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->resolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(Request $request)
    {
        $processor = $this->prepareProcessor();

        /** @var ObjectIteratorInterface|FormInterface $result */
        $result = $processor->processRequest($request);
        if (! $result instanceof ObjectIteratorInterface) {
            return $result;
        }

        return $result->apply($this->prepareResultCallable());
    }

    /**
     * Creates a Processor that handles the current list request.
     */
    protected function prepareProcessor(array $options = []): Processor
    {
        return new Processor($this->prepareQueryBuilder(), $this->formFactory, \array_merge($options, [
            'order_field' => 'order',
            'skip_field' => 'skip',
            'limit_field' => 'limit',
            'continuation_token' => [
                'field' => 'continue',
                'checksum_field' => '_id',
            ],
        ]));
    }

    /**
     * Creates the query builder that will retrieve the results.
     */
    abstract protected function prepareQueryBuilder(): QueryBuilder;

    /**
     * Returns the callable applied on every result for the current request.
     */
    abstract protected function prepareResultCallable(): callable;
}
