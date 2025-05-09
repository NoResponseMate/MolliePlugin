<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\MolliePlugin\Payum\Action\Subscription;

use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Payum\Action\BaseApiAwareAction;
use Sylius\MolliePlugin\Payum\Request\Subscription\StatusRecurringSubscription;
use Sylius\MolliePlugin\StateMachine\Applicator\StateMachineCompatibilityLayer;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicatorInterface;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionTransitions;
use Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransitionInterface;

final class StatusRecurringSubscriptionAction extends BaseApiAwareAction
{
    public function __construct(
        private readonly EntityManagerInterface $subscriptionManager,
        private readonly SubscriptionAndPaymentIdApplicatorInterface $subscriptionAndPaymentIdApplicator,
        private readonly SubscriptionAndSyliusPaymentApplicatorInterface $subscriptionAndSyliusPaymentApplicator,
        private readonly StateMachineTransitionInterface|StateMachineInterface $stateMachineTransition,
    ) {
        if ($this->stateMachineTransition instanceof StateMachineTransitionInterface) {
            trigger_deprecation(
                'sylius/mollie-plugin',
                '2.2',
                sprintf(
                    'Passing an instance of "%s" as the fourth argument is deprecated. It will accept only instances of "%s" in MolliePlugin 3.0. The argument name will change from "stateMachineTransition" to "stateMachine".',
                    StateMachineTransitionInterface::class,
                    StateMachineInterface::class,
                ),
            );
        }
    }

    /** @param StatusRecurringSubscription|mixed $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var MollieSubscriptionInterface $subscription */
        $subscription = $request->getModel();
        $paymentId = $request->getPaymentId();
        $syliusPayment = $request->getPayment();

        if (null !== $paymentId) {
            $this->subscriptionAndPaymentIdApplicator->execute($subscription, $paymentId);
        }

        if (null !== $syliusPayment) {
            $this->subscriptionAndSyliusPaymentApplicator->execute($subscription, $syliusPayment);
        }

        $this->applySubscriptionStateMachine($subscription, MollieSubscriptionTransitions::TRANSITION_COMPLETE);
        $this->applySubscriptionStateMachine($subscription, MollieSubscriptionTransitions::TRANSITION_ABORT);

        $this->subscriptionManager->persist($subscription);
        $this->subscriptionManager->flush();
    }

    public function supports($request): bool
    {
        return
            $request instanceof StatusRecurringSubscription &&
            $request->getModel() instanceof MollieSubscriptionInterface;
    }

    private function applySubscriptionStateMachine(MollieSubscriptionInterface $subscription, string $transition): void
    {
        if ($this->stateMachineTransition instanceof StateMachineInterface) {
            StateMachineCompatibilityLayer::apply($this->stateMachineTransition, $subscription, MollieSubscriptionTransitions::GRAPH, $transition);
        } else {
            $this->stateMachineTransition->apply($subscription, $transition);
        }
    }
}
