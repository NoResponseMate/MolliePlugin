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

namespace Sylius\MolliePlugin\StateMachine\Transition;

use SM\Factory\FactoryInterface;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Abstraction\StateMachine\WinzouStateMachineAdapter;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionPaymentProcessingTransitions;

trigger_deprecation(
    'sylius/mollie-plugin',
    '2.1',
    'The "%s" class is deprecated and will be removed in Mollie 3.0. Use "%s" instead.',
    PaymentStateMachineTransition::class,
    StateMachineInterface::class,
);
final class PaymentStateMachineTransition implements PaymentStateMachineTransitionInterface
{
    public function __construct(private readonly FactoryInterface|StateMachineInterface $subscriptionStateMachineFactory)
    {
    }

    public function apply(
        MollieSubscriptionInterface $subscription,
        string $transitions,
    ): void {
        $stateMachine = $this->getStateMachine();

        if (!$stateMachine->can($subscription, MollieSubscriptionPaymentProcessingTransitions::GRAPH, $transitions)) {
            return;
        }

        $stateMachine->apply($subscription, MollieSubscriptionPaymentProcessingTransitions::GRAPH, $transitions);
    }

    private function getStateMachine(): StateMachineInterface
    {
        if ($this->subscriptionStateMachineFactory instanceof FactoryInterface) {
            return new WinzouStateMachineAdapter($this->subscriptionStateMachineFactory);
        }

        return $this->subscriptionStateMachineFactory;
    }
}
