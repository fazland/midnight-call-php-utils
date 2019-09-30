<?php declare(strict_types=1);

namespace MidnightCall\Utils\DependencyInjection\CompilerPass;

use MidnightCall\Utils\Doctrine\ConnectionFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterDoctrineEnumTypePass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $enumTagName;

    public function __construct(string $enumTagName)
    {
        $this->enumTagName = $enumTagName;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $types = [];
        foreach ($container->findTaggedServiceIds($this->enumTagName) as $serviceId => $unused) {
            $class = $container->getDefinition($serviceId)->getClass();
            $types[$class] = $class;

            $container->removeDefinition($serviceId);
        }

        $container->getDefinition(ConnectionFactory::class)
            ->addMethodCall('setEnums', [$types])
        ;

        $container->findDefinition('doctrine.dbal.connection')
            ->setFactory([new Reference(ConnectionFactory::class), 'createConnection'])
        ;
    }
}
