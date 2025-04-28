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

namespace Sylius\MolliePlugin\EventListener\Workflow\SyliusOrderCheckout;

use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Processor\PaymentSurchargeProcessorInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Webmozart\Assert\Assert;

final class ApplyPaymentSurchargeListener
{
    public function __construct(private readonly PaymentSurchargeProcessorInterface $processor)
    {
    }

    public function __invoke(TransitionEvent $event): void
    {
        $order = $event->getSubject();
        Assert::isInstanceOf($order, OrderInterface::class);

        $this->processor->process($order);
    }
}
