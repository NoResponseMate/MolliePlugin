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
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionTransitions;

trigger_deprecation(
    'sylius/mollie-plugin',
    '2.2',
    'The "%s" class is deprecated and will be removed in MolliePlugin 3.0. Use "%s" instead.',
    StateMachineTransition::class,
    StateMachineInterface::class,
);
final class StateMachineTransition implements StateMachineTransitionInterface
{
    public function __construct(private readonly FactoryInterface|StateMachineInterface $subscriptionStateMachineFactory)
    {
    }

    public function apply(MollieSubscriptionInterface $subscription, string $transitions): void
    {
        $stateMachine = $this->getStateMachine();

        if (!$stateMachine->can($subscription, MollieSubscriptionTransitions::GRAPH, $transitions)) {
            return;
        }

        $stateMachine->apply($subscription, MollieSubscriptionTransitions::GRAPH, $transitions);
    }

    private function getStateMachine(): StateMachineInterface
    {
        if ($this->subscriptionStateMachineFactory instanceof FactoryInterface) {
            return new WinzouStateMachineAdapter($this->subscriptionStateMachineFactory);
        }

        return $this->subscriptionStateMachineFactory;
    }
}
