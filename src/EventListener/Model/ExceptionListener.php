<?php declare(strict_types=1);

namespace MidnightCall\Utils\EventListener\Model;

use MidnightCall\Utils\Model\Exception\ExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{
    public function onException(ExceptionEvent $event): void
    {
        $exception = $event->getException();
        if (! $exception instanceof ExceptionInterface) {
            return;
        }

        try {
            $response = $exception->buildResponse();
            $event->setResponse($response);
        } catch (\Throwable $e) {
            $event->setException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onException', 8],
        ];
    }
}
