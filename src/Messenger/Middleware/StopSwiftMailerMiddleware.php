<?php declare(strict_types=1);

namespace MidnightCall\Utils\Messenger\Middleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class StopSwiftMailerMiddleware implements MiddlewareInterface
{
    /**
     * @var \Swift_Mailer[]
     */
    private $mailers;

    public function __construct(\Swift_Mailer ...$mailers)
    {
        $this->mailers = $mailers;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $result = $stack->next()->handle($envelope, $stack);

        foreach ($this->mailers as $mailer) {
            $transport = $mailer->getTransport();

            if ($transport->isStarted()) {
                $transport->stop();
            }
        }

        return $result;
    }
}
