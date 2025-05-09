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

namespace Sylius\MolliePlugin\StateMachine\Applicator;

use Mollie\Api\Types\PaymentStatus;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionPaymentProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionTransitions;
use Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransitionInterface;
use Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransitionInterface;
use Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransitionInterface;

final class SubscriptionAndPaymentIdApplicator implements SubscriptionAndPaymentIdApplicatorInterface
{
    public function __construct(
        private readonly MollieApiClient $mollieApiClient,
        private readonly StateMachineTransitionInterface|StateMachineInterface $stateMachineTransition,
        private readonly ?PaymentStateMachineTransitionInterface $paymentStateMachineTransition = null,
        private readonly ?ProcessingStateMachineTransitionInterface $processingStateMachineTransition = null,
    ) {
        if ($this->stateMachineTransition instanceof StateMachineTransitionInterface) {
            trigger_deprecation(
                'sylius/mollie-plugin',
                '2.2',
                sprintf(
                    'Passing an instance of "%s" as the second argument is deprecated. It will accept only instances of "%s" in MolliePlugin 3.0. The argument name will change from "stateMachineTransition" to "stateMachine".',
                    StateMachineTransitionInterface::class,
                    StateMachineInterface::class,
                ),
            );
        }

        if (null !== $this->paymentStateMachineTransition) {
            trigger_deprecation(
                'sylius/mollie-plugin',
                '2.2',
                sprintf(
                    'Passing an instance of "%s" as the third argument is deprecated and will be prohibited in MolliePlugin 3.0.',
                    PaymentStateMachineTransitionInterface::class,
                ),
            );
        }

        if (null !== $this->processingStateMachineTransition) {
            trigger_deprecation(
                'sylius/mollie-plugin',
                '2.2',
                sprintf(
                    'Passing an instance of "%s" as the fourth argument is deprecated and will be prohibited in MolliePlugin 3.0.',
                    ProcessingStateMachineTransitionInterface::class,
                ),
            );
        }
    }

    public function execute(
        MollieSubscriptionInterface $subscription,
        string $paymentId,
    ): void {
        $configuration = $subscription->getSubscriptionConfiguration();
        $payment = $this->mollieApiClient->payments->get($paymentId);

        if (null === $configuration->getMandateId()) {
            $configuration->setMandateId($payment->mandateId);
        }

        if (null === $configuration->getCustomerId()) {
            $configuration->setCustomerId($payment->customerId);
        }

        switch ($payment->status) {
            case PaymentStatus::STATUS_OPEN:
            case PaymentStatus::STATUS_PENDING:
            case PaymentStatus::STATUS_AUTHORIZED:

                $this->applyPaymentStateMachine($subscription, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
                $this->applySubscriptionStateMachine($subscription, MollieSubscriptionTransitions::TRANSITION_PROCESS);

                break;
            case PaymentStatus::STATUS_PAID:
                $subscription->resetFailedPaymentCount();
                $this->applySubscriptionStateMachine($subscription, MollieSubscriptionTransitions::TRANSITION_ACTIVATE);

                $this->applyPaymentStateMachine($subscription, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_SUCCESS);
                $this->applyProcessingStateMachine($subscription, MollieSubscriptionProcessingTransitions::TRANSITION_SCHEDULE);

                break;
            default:
                $subscription->incrementFailedPaymentCounter();
                $this->applyPaymentStateMachine($subscription, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_FAILURE);

                break;
        }
    }

    private function applySubscriptionStateMachine(MollieSubscriptionInterface $subscription, string $transition): void
    {
        if ($this->stateMachineTransition instanceof StateMachineInterface) {
            StateMachineCompatibilityLayer::apply($this->stateMachineTransition, $subscription, MollieSubscriptionTransitions::GRAPH, $transition);
        } else {
            $this->stateMachineTransition->apply($subscription, $transition);
        }
    }

    private function applyPaymentStateMachine(MollieSubscriptionInterface $subscription, string $transition): void
    {
        if ($this->stateMachineTransition instanceof StateMachineInterface) {
            StateMachineCompatibilityLayer::apply($this->stateMachineTransition, $subscription, MollieSubscriptionPaymentProcessingTransitions::GRAPH, $transition);
        } else {
            $this->paymentStateMachineTransition->apply($subscription, $transition);
        }
    }

    private function applyProcessingStateMachine(MollieSubscriptionInterface $subscription, string $transition): void
    {
        if ($this->stateMachineTransition instanceof StateMachineInterface) {
            StateMachineCompatibilityLayer::apply($this->stateMachineTransition, $subscription, MollieSubscriptionProcessingTransitions::GRAPH, $transition);
        } else {
            $this->processingStateMachineTransition->apply($subscription, $transition);
        }
    }
}
