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

use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;

trigger_deprecation(
    'sylius/mollie-plugin',
    '2.2',
    'The "%s" class is deprecated and will be removed in MolliePlugin 3.0. Use "%s" instead.',
    PaymentStateMachineTransitionInterface::class,
    StateMachineInterface::class,
);
interface PaymentStateMachineTransitionInterface
{
    public function apply(MollieSubscriptionInterface $subscription, string $transitions): void;
}
