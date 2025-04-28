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

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Subscription\Processor\SubscriptionPaymentProcessorInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Webmozart\Assert\Assert;

final class SubscriptionPaymentFailListener
{
    public function __construct(private readonly SubscriptionPaymentProcessorInterface $processor)
    {
    }

    public function __invoke(TransitionEvent $event): void
    {
        $payment = $event->getSubject();
        Assert::isInstanceOf($payment, PaymentInterface::class);

        $this->processor->processFailed($payment);
    }
}
