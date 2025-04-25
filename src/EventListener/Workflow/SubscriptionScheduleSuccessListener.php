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

namespace Sylius\MolliePlugin\EventListener\Workflow;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Subscription\Processor\SubscriptionScheduleProcessorInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Webmozart\Assert\Assert;

final class SubscriptionScheduleSuccessListener
{
    public function __construct(private readonly SubscriptionScheduleProcessorInterface $processor)
    {
    }

    public function __invoke(TransitionEvent $event): void
    {
        $subscription = $event->getSubject();
        Assert::isInstanceOf($subscription, MollieSubscriptionInterface::class);

        $this->processor->process($subscription);
    }
}
