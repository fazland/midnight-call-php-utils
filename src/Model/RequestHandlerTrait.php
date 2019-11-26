<?php declare(strict_types=1);

namespace MidnightCall\Utils\Model;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @property FormFactoryInterface $formFactory
 *
 * @method string getTypeClass
 * @method void commit
 */
trait RequestHandlerTrait
{
    /**
     * {@inheritdoc}
     */
    public function handleRequest(Request $request): object
    {
        $form = $this->formFactory->createNamed(null, $this->getTypeClass(), $this);
        $form->handleRequest($request);
        if (! $form->isSubmitted() || ! $form->isValid()) {
            return $form;
        }

        $this->commit();

        return $this;
    }
}
